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

    public function createGroup(int $creatorId, string $title, array $recipientIds): array {
        $title = trim($title);

        if ($creatorId < 1) {
            throw new \InvalidArgumentException('Invalid group creator.');
        }

        if ($title === '' || mb_strlen($title, 'UTF-8') > 150) {
            throw new \InvalidArgumentException('Enter a group name between 1 and 150 characters.');
        }

        foreach ($recipientIds as $recipientId) {
            if (!is_int($recipientId) || $recipientId < 1) {
                throw new \InvalidArgumentException('Invalid group member.');
            }
        }

        $memberIds = array_values(array_unique([
            $creatorId,
            ...$recipientIds
        ]));

        sort($memberIds, SORT_NUMERIC);

        if (count($memberIds) < 3 || count($memberIds) > 50) {
            throw new \InvalidArgumentException('A group requires between 3 and 50 members, including you.');
        }

        if ($this->pdo->inTransaction()) {
            throw new \LogicException('Group creation requires its own transaction.');
        }

        $this->pdo->beginTransaction();

        try {
            /*
            * Check and lock users in a consistent order.
            * No conversation is created unless every member is eligible.
            */
            $sql = "SELECT user_id, role, account_status
                    FROM users
                    WHERE user_id = :user_id
                    FOR UPDATE";
            $stmt = $this->pdo->prepare($sql);

            foreach ($memberIds as $memberId) {
                $stmt->execute([
                    ':user_id' => $memberId
                ]);

                $user = $stmt->fetch();

                if ($user === false || $user['account_status'] !== 'active') {
                    throw new \DomainException('Every group member must have an active account.');
                }

                if ($memberId === $creatorId) {
                    if ($user['role'] !== 'driver') {
                        throw new \DomainException('Messenger requires an active driver account.');
                    }
                } elseif (!in_array($user['role'], ['driver', 'dispatch'], true)) {
                    throw new \DomainException('Choose drivers or dispatch contacts for this group.');
                }
            }

            $sql = "INSERT INTO conversations (conversation_type, title, direct_key, created_by)
                    VALUES ('group', :title, NULL, :created_by)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':title' => $title,
                ':created_by' => $creatorId
            ]);

            $conversationId = (int) $this->pdo->lastInsertId();

            if ($conversationId < 1) {
                throw new \RuntimeException('The group could not be created.');
            }

            $sql = "INSERT INTO conversation_members (conversation_id, user_id)
                    VALUES (:conversation_id, :user_id)";
            $stmt = $this->pdo->prepare($sql);

            foreach ($memberIds as $memberId) {
                $stmt->execute([
                    ':conversation_id' => $conversationId,
                    ':user_id' => $memberId
                ]);
            }

            $sql = "SELECT conversation_id, conversation_type, title, created_by, created_at, last_message_at
                    FROM conversations
                    WHERE conversation_id = :conversation_id";
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':conversation_id' => $conversationId
            ]);

            $conversation = $stmt->fetch();

            if ($conversation === false) {
                throw new \RuntimeException('The group could not be loaded.');
            }

            $conversation['conversation_id'] = (int) $conversation['conversation_id'];
            $conversation['created_by'] = (int) $conversation['created_by'];
            $conversation['member_count'] = count($memberIds);

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

        $sql = "SELECT c.conversation_id, c.conversation_type, c.title, latest.created_at AS last_message_at, cm.last_read_message_id, cm.cleared_through_message_id, cm.deleted_at, latest.message_id AS latest_message_id, latest.sender_id AS latest_sender_id, latest.message_body AS latest_message_body, latest.created_at AS latest_message_created_at,
                (
                SELECT COUNT(*) FROM messages AS m
                WHERE m.conversation_id = c.conversation_id
                AND m.sender_id <> cm.user_id
                AND m.message_id > GREATEST
                    (
                        COALESCE(cm.last_read_message_id, 0),
                        cm.cleared_through_message_id
                    )
                ) 
                AS unread_count FROM conversation_members 
                AS cm INNER JOIN conversations AS c
                ON c.conversation_id = cm.conversation_id
                LEFT JOIN messages AS latest
                ON latest.message_id = (
                    SELECT newest.message_id
                    FROM messages AS newest
                    WHERE newest.conversation_id = c.conversation_id
                    AND newest.message_id > cm.cleared_through_message_id
                    ORDER BY newest.message_id DESC
                    LIMIT 1
                )
                WHERE cm.user_id = :member_id
                AND (
                        cm.deleted_at IS NULL
                        OR latest.message_id IS NOT NULL
                    )
                ORDER BY latest.created_at DESC, latest.message_id DESC, c.conversation_id DESC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':member_id' => $memberId
        ]);

        $summaries = $stmt->fetchAll();
        foreach ($summaries as &$summary) {
            $summary['conversation_id'] = (int) $summary['conversation_id'];
            $summary['unread_count'] = (int) $summary['unread_count'];
            $summary['last_read_message_id'] = $summary['last_read_message_id'] !== null ? (int) $summary['last_read_message_id'] : null;
            $summary['latest_message_id'] = $summary['latest_message_id'] !== null ? (int) $summary['latest_message_id'] : null;
            $summary['latest_sender_id'] = $summary['latest_sender_id'] !== null ? (int) $summary['latest_sender_id'] : null;
            $summary['cleared_through_message_id'] = (int) $summary['cleared_through_message_id'];
        }

        unset($summary);
        return $summaries;
    }

    public function findOtherMembersForMember(int $memberId): array {
        if ($memberId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger member ID.');
        }

        $sql = "SELECT other_member.conversation_id, other_member.user_id, u.first_name, u.last_name, u.role
                FROM conversation_members AS own_member
                INNER JOIN conversations AS c
                ON c.conversation_id = own_member.conversation_id
                INNER JOIN conversation_members AS other_member
                ON other_member.conversation_id = own_member.conversation_id
                AND other_member.user_id <> own_member.user_id
                INNER JOIN users AS u
                ON u.user_id = other_member.user_id
                WHERE own_member.user_id = :member_id
                AND c.conversation_type IN ('direct', 'group')";
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

    public function deleteForMember(int $memberId, array $conversationIds): array {
        if ($memberId < 1 || $conversationIds === []) {
            throw new \InvalidArgumentException('Invalid conversation selection.');
        }

        foreach ($conversationIds as $conversationId) {
            if (!is_int($conversationId) || $conversationId < 1) {
                throw new \InvalidArgumentException('Invalid conversation ID.');
            }
        }

        $conversationIds = array_values(array_unique($conversationIds));
        sort($conversationIds, SORT_NUMERIC);

        if (count($conversationIds) > 100) {
            throw new \InvalidArgumentException('Select no more than 100 conversations.');
        }

        if ($this->pdo->inTransaction()) {
            throw new \LogicException('Conversation deletion requires its own transaction.');
        }

        $this->pdo->beginTransaction();

        try {
            $deletedConversations = [];

            foreach ($conversationIds as $conversationId) {
                // Match send(): lock the conversation before its membership.
                $sql = "SELECT conversation_id FROM conversations
                        WHERE conversation_id = :conversation_id
                        FOR UPDATE";
                $stmt = $this->pdo->prepare($sql);

                $stmt->execute([
                    ':conversation_id' => $conversationId
                ]);

                if ($stmt->fetchColumn() === false) {
                    throw new \DomainException('A selected conversation is unavailable.');
                }

                $sql = "SELECT cleared_through_message_id, last_read_message_id
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
                    throw new \DomainException('You cannot delete a selected conversation.');
                }

                $sql = "SELECT message_id FROM messages
                        WHERE conversation_id = :conversation_id
                        ORDER BY message_id DESC
                        LIMIT 1
                        FOR UPDATE";
                $stmt = $this->pdo->prepare($sql);

                $stmt->execute([
                    ':conversation_id' => $conversationId
                ]);

                $latestMessageId = $stmt->fetchColumn();

                $cutoff = max((int) $membership['cleared_through_message_id'], $latestMessageId !== false ? (int) $latestMessageId : 0);

                $readPosition = max((int) ($membership['last_read_message_id'] ?? 0), $cutoff);

                $sql = "UPDATE conversation_members
                        SET cleared_through_message_id = :cutoff, last_read_message_id = :read_position, deleted_at = CURRENT_TIMESTAMP
                        WHERE conversation_id = :conversation_id
                        AND user_id = :member_id";
                $stmt = $this->pdo->prepare($sql);

                $stmt->execute([
                    ':cutoff' => $cutoff,
                    ':read_position' => $readPosition,
                    ':conversation_id' => $conversationId,
                    ':member_id' => $memberId
                ]);

                $deletedConversations[] = [
                    'conversation_id' => $conversationId,
                    'cleared_through_message_id' => $cutoff
                ];
            }

            $this->pdo->commit();
            return $deletedConversations;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findAdminConversationForDriver(int $driverId, int $adminId): ?array {
        if ($driverId < 1 || $adminId < 1 || $driverId === $adminId) {
            throw new \InvalidArgumentException('Invalid Messenger identity.');
        }

        $directKey = min($driverId, $adminId) . ':' . max($driverId, $adminId);

        $sql = "SELECT c.conversation_id, c.conversation_type, c.created_by, c.created_at, c.last_message_at
                FROM conversations AS c
                INNER JOIN conversation_members AS driver_member
                ON driver_member.conversation_id = c.conversation_id
                AND driver_member.user_id = :driver_id
                INNER JOIN conversation_members AS admin_member
                ON admin_member.conversation_id = c.conversation_id
                AND admin_member.user_id = :admin_id
                WHERE c.direct_key = :direct_key
                AND c.conversation_type = 'direct'
                AND c.created_by = admin_member.user_id
                AND (
                    SELECT COUNT(*)
                    FROM conversation_members AS members
                    WHERE members.conversation_id = c.conversation_id
                ) = 2
                AND EXISTS (
                    SELECT 1 FROM messages AS m
                    WHERE m.conversation_id = c.conversation_id
                    AND m.sender_id = admin_member.user_id
                    AND m.message_id > driver_member.cleared_through_message_id
                )
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':driver_id' => $driverId,
            ':admin_id' => $adminId,
            ':direct_key' => $directKey
        ]);

        $conversation = $stmt->fetch();

        if ($conversation === false) {
            return null;
        }

        $conversation['conversation_id'] = (int) $conversation['conversation_id'];
        $conversation['created_by'] = (int) $conversation['created_by'];

        return $conversation;
    }

    public function findGroupMembersForMember(int $conversationId, int $memberId): array {
        if ($conversationId < 1 || $memberId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger identity.');
        }

        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.role, cm.joined_at, c.created_by
                FROM conversations AS c
                INNER JOIN conversation_members AS own_member
                ON own_member.conversation_id = c.conversation_id
                AND own_member.user_id = :member_id
                INNER JOIN conversation_members AS cm
                ON cm.conversation_id = c.conversation_id
                INNER JOIN users AS u
                ON u.user_id = cm.user_id
                WHERE c.conversation_id = :conversation_id
                AND c.conversation_type = 'group'
                ORDER BY u.user_id ASC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':member_id' => $memberId,
            ':conversation_id' => $conversationId
        ]);

        $members = $stmt->fetchAll();

        if ($members === []) {
            throw new \DomainException('The group is unavailable or you are not a member.');
        }

        foreach ($members as &$member) {
            $member['user_id'] = (int) $member['user_id'];
            $member['created_by'] = (int) $member['created_by'];
        }

        unset($member);

        return $members;
    }
}

?>