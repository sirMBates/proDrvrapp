<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;
use Throwable;

class ConversationRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function findOrCreateDirect(int $initiatorId, int $recipientId): array {
        if ($initiatorId < 1 || $recipientId < 1 || $initiatorId === $recipientId) {
            throw new \InvalidArgumentException('Two different valid users are required.');
        }

        if ($this->pdo->inTransaction()) {
            throw new \LogicException('Direct conversation creation requires its own transaction.');
        }

        $memberIds = [
            min($initiatorId, $recipientId),
            max($initiatorId, $recipientId)
        ];

        $directKey = $memberIds[0] . ':' . $memberIds[1];

        $this->pdo->beginTransaction();

        try {
            // The unique direct_key also handles simultaneous requests.
            $sql = "INSERT INTO conversations (
                        conversation_type,
                        direct_key,
                        created_by
                    )
                    VALUES ('direct', :direct_key, :created_by)
                    ON DUPLICATE KEY UPDATE
                        conversation_id = LAST_INSERT_ID(conversation_id)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':direct_key' => $directKey,
                ':created_by' => $initiatorId
            ]);

            $conversationId = (int) $this->pdo->lastInsertId();

            $sql2 = "SELECT user_id
                    FROM conversation_members
                    WHERE conversation_id = :conversation_id
                    ORDER BY user_id ASC";

            $stmt = $this->pdo->prepare($sql2);

            $stmt->execute([
                ':conversation_id' => $conversationId
            ]);

            $existingMembers = array_map(static fn ($id): int => (int) $id, $stmt->fetchAll(PDO::FETCH_COLUMN));

            if ($existingMembers === []) {
                $sql = "INSERT INTO conversation_members (
                        conversation_id,
                        user_id
                        )
                        VALUES (:conversation_id, :user_id)";
                $stmt = $this->pdo->prepare($sql);

                foreach ($memberIds as $memberId) {
                    $stmt->execute([
                        ':conversation_id' => $conversationId,
                        ':user_id' => $memberId
                    ]);
                }
            } elseif ($existingMembers !== $memberIds) {
                throw new \RuntimeException('Direct conversation membership is inconsistent.');
            }

            $sql3 = "SELECT conversation_id, conversation_type, created_by, created_at, last_message_at
                    FROM conversations
                    WHERE conversation_id = :conversation_id";

            $stmt = $this->pdo->prepare($sql3);

            $stmt->execute([
                ':conversation_id' => $conversationId
            ]);

            $conversation = $stmt->fetch();
            if ($conversation === false) {
                throw new \RuntimeException('Conversation could not be loaded.');
            }

            $conversation['conversation_id'] = (int) $conversation['conversation_id'];
            $conversation['created_by'] = (int) $conversation['created_by'];

            $this->pdo->commit();
            return $conversation;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findUnreadSummariesForMember(int $memberId): array {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger member ID.');
        }

        $sql = "SELECT c.conversation_id, c.conversation_type, c.last_message_at, cm.last_read_message_id,
                (
                SELECT COUNT(*)
                FROM messages AS m
                WHERE m.conversation_id = c.conversation_id
                AND m.sender_id <> cm.user_id
                AND m.message_id > COALESCE(cm.last_read_message_id, 0)
                ) AS unread_count
                FROM conversation_members AS cm
                INNER JOIN conversations AS c
                    ON c.conversation_id = cm.conversation_id
                WHERE cm.user_id = :member_id
                ORDER BY c.last_message_at DESC, c.conversation_id DESC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':member_id' => $memberId
        ]);

        $summaries = $stmt->fetchAll();
        foreach ($summaries as &$summary) {
            $summary['conversation_id'] = (int) $summary['conversation_id'];
            $summary['unread_count'] = (int) $summary['unread_count'];
            $summary['last_read_message_id'] = $summary['last_read_message_id'] !== null ? (int) $summary['last_read_message_id'] : null;
        }

        unset($summary);
        return $summaries;
    }

    public function findOtherMembersForMember(int $memberId): array {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger member ID.');
        }

        $sql = "SELECT other_member.conversation_id, other_member.user_id
                FROM conversation_members AS own_member
                INNER JOIN conversations AS c
                    ON c.conversation_id = own_member.conversation_id
                INNER JOIN conversation_members AS other_member
                    ON other_member.conversation_id = own_member.conversation_id
                AND other_member.user_id <> own_member.user_id
                WHERE own_member.user_id = :member_id
                AND c.conversation_type = 'direct'";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':member_id' => $memberId
        ]);

        return $stmt->fetchAll();
    }

    public function markReadForMember(int $conversationId, int $memberId, int $messageId): int {
        if ($conversationId < 1 || $memberId < 1 || $messageId < 1) {
            throw new \InvalidArgumentException('Invalid read position.');
        }

        if ($this->pdo->inTransaction()) {
            throw new \LogicException('Marking messages read requires its own transaction.');
        }

        $this->pdo->beginTransaction();

        try {
            $sql = "SELECT last_read_message_id
                    FROM conversation_members
                    WHERE conversation_id = :conversation_id
                    AND user_id = :member_id
                    FOR UPDATE";
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':member_id' => $memberId
            ]);

            $membership = $stmt->fetch();
            if ($membership === false) {
                throw new \DomainException('You are not a member of this conversation.');
            }

            // The requested message must belong to this conversation.
            $sql = "SELECT message_id
                    FROM messages
                    WHERE conversation_id = :conversation_id
                    AND message_id = :message_id
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':message_id' => $messageId
            ]);

            if ($stmt->fetchColumn() === false) {
                throw new \InvalidArgumentException('The read position does not belong to this conversation.');
            }

            $currentPosition = (int) ($membership['last_read_message_id'] ?? 0);

            $readPosition = max($currentPosition, $messageId);

            if ($readPosition > $currentPosition) {
                $sql = "UPDATE conversation_members
                        SET last_read_message_id = :message_id
                        WHERE conversation_id = :conversation_id
                        AND user_id = :member_id";
                $stmt = $this->pdo->prepare($sql);

                $stmt->execute([
                    ':message_id' => $readPosition,
                    ':conversation_id' => $conversationId,
                    ':member_id' => $memberId
                ]);
            }

            $this->pdo->commit();

            return $readPosition;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}

?>