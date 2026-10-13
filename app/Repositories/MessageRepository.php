<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Validation\Validator;
use Core\Database;
use PDO;
use Throwable;

class MessageRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function send(int $conversationId, int $senderId, string $clientMessageId, string $messageBody): array {
        $clientMessageId = trim($clientMessageId);
        $messageBody = trim($messageBody);

        if ($conversationId < 1 || $senderId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger identity.');
        }

        if (!Validator::uuid($clientMessageId)) {
            throw new \InvalidArgumentException('Invalid client message ID.');
        }

        if ($messageBody === '') {
            throw new \InvalidArgumentException('A message is required.');
        }

        if ($this->pdo->inTransaction()) {
            throw new \LogicException('Message sending requires its own transaction.');
        }

        $this->pdo->beginTransaction();

        try {
            $conversationSql = "SELECT conversation_id
                                FROM conversations
                                WHERE conversation_id = :conversation_id
                                FOR UPDATE";
            $stmt = $this->pdo->prepare($conversationSql);

            $stmt->execute([
                ':conversation_id' => $conversationId
            ]);

            if ($stmt->fetchColumn() === false) {
                throw new \DomainException('The conversation is unavailable.');
            }

            $membershipSql = "SELECT conversation_id, cleared_through_message_id
                            FROM conversation_members
                            WHERE conversation_id = :conversation_id
                            AND user_id = :sender_id
                            FOR UPDATE";
            $stmt = $this->pdo->prepare($membershipSql);

            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':sender_id' => $senderId
            ]);

            $membership = $stmt->fetch();

            if ($membership === false) {
                throw new \DomainException('You are not a member of this conversation.');
            }

            $insertSql = "INSERT INTO messages (conversation_id, sender_id, client_message_id, message_body)
                        VALUES (:conversation_id, :sender_id, :client_message_id, :message_body)
                        ON DUPLICATE KEY UPDATE
                        message_id = LAST_INSERT_ID(message_id)";
            $stmt = $this->pdo->prepare($insertSql);

            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':sender_id' => $senderId,
                ':client_message_id' => $clientMessageId,
                ':message_body' => $messageBody
            ]);

            $messageId = (int) $this->pdo->lastInsertId();
            if ($messageId < 1) {
                throw new \RuntimeException('The message could not be created.');
            }

            $messageSql = "SELECT message_id, conversation_id, sender_id, client_message_id, message_body, created_at
                        FROM messages
                        WHERE message_id = :message_id
                        LIMIT 1";
            $stmt = $this->pdo->prepare($messageSql);

            $stmt->execute([
                ':message_id' => $messageId
            ]);

            $message = $stmt->fetch();
            if ($message === false) {
                throw new \RuntimeException('The saved message could not be loaded.');
            }

            if ((int) $message['conversation_id'] !== $conversationId || (int) $message['sender_id'] !== $senderId || !hash_equals((string) $message['client_message_id'], $clientMessageId) || (string) $message['message_body'] !== $messageBody) {
                throw new \DomainException('The client message ID has already been used.');
            }

            if ($messageId <= (int) $membership['cleared_through_message_id']) {
                throw new \DomainException('This message belongs to cleared history. Start a new message.');
            }

            $updateSql = "UPDATE conversations
                        SET last_message_at =
                        CASE
                        WHEN last_message_at IS NULL
                            OR last_message_at < :created_at
                        THEN :created_at_update
                        ELSE last_message_at
                        END
                        WHERE conversation_id = :conversation_id";
            $stmt = $this->pdo->prepare($updateSql);

            $stmt->execute([
                ':created_at' => $message['created_at'],
                ':created_at_update' => $message['created_at'],
                ':conversation_id' => $conversationId
            ]);

            $message['message_id'] = (int) $message['message_id'];
            $message['conversation_id'] = (int) $message['conversation_id'];
            $message['sender_id'] = (int) $message['sender_id'];

            $this->pdo->commit();

            return $message;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findForMember(int $conversationId, int $memberId, int $afterMessageId = 0, int $limit = 50, int $beforeMessageId = 0): array {
        if ($conversationId < 1 || $memberId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger identity.');
        }

        if ($afterMessageId < 0 || $beforeMessageId < 0) {
            throw new \InvalidArgumentException('Invalid message cursor.');
        }

        if ($afterMessageId > 0 && $beforeMessageId > 0) {
            throw new \InvalidArgumentException('Use either an after cursor or a before cursor.');
        }

        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('Invalid message limit.');
        }

        $membershipSql = "SELECT 1 FROM conversation_members
                        WHERE conversation_id = :conversation_id
                        AND user_id = :member_id
                        LIMIT 1";
        $stmt = $this->pdo->prepare($membershipSql);

        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':member_id' => $memberId
        ]);

        if ($stmt->fetchColumn() === false) {
            throw new \DomainException('You are not a member of this conversation.');
        }

        if ($afterMessageId > 0) {
            $sql = "SELECT m.message_id, m.conversation_id, m.sender_id, m.client_message_id, m.message_body, m.created_at
                    FROM messages AS m
                    INNER JOIN conversation_members AS cm
                    ON cm.conversation_id = m.conversation_id
                    AND cm.user_id = :member_id
                    WHERE m.conversation_id = :conversation_id
                    AND m.message_id > cm.cleared_through_message_id
                    AND m.message_id > :after_message_id
                    ORDER BY m.message_id ASC
                    LIMIT :limit";
        } elseif ($beforeMessageId > 0) {
            $sql = "SELECT m.message_id, m.conversation_id, m.sender_id, m.client_message_id, m.message_body, m.created_at
                    FROM messages AS m
                    INNER JOIN conversation_members AS cm
                    ON cm.conversation_id = m.conversation_id
                    AND cm.user_id = :member_id
                    WHERE m.conversation_id = :conversation_id
                    AND m.message_id > cm.cleared_through_message_id
                    AND m.message_id < :before_message_id
                    ORDER BY m.message_id DESC
                    LIMIT :limit";
        } else {
            $sql = "SELECT m.message_id, m.conversation_id, m.sender_id, m.client_message_id, m.message_body, m.created_at
                    FROM messages AS m
                    INNER JOIN conversation_members AS cm
                    ON cm.conversation_id = m.conversation_id
                    AND cm.user_id = :member_id
                    WHERE m.conversation_id = :conversation_id
                    AND m.message_id > cm.cleared_through_message_id
                    ORDER BY m.message_id DESC
                    LIMIT :limit";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);

        if ($afterMessageId > 0) {
            $stmt->bindValue(':after_message_id', $afterMessageId, PDO::PARAM_INT);
        }

        if ($beforeMessageId > 0) {
            $stmt->bindValue(':before_message_id', $beforeMessageId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $messages = $stmt->fetchAll();

        // Initial and older-history pages were selected newest first.
        if ($afterMessageId === 0) {
            $messages = array_reverse($messages);
        }

        return array_map(static function (array $message): array {
            $message['message_id'] = (int) $message['message_id'];
            $message['conversation_id'] = (int) $message['conversation_id'];
            $message['sender_id'] = (int) $message['sender_id'];

            return $message;
        }, $messages);
    }

    public function hasMessagesBefore(int $conversationId, int $messageId, int $memberId): bool {
        if ($conversationId < 1 || $messageId < 1 || $memberId < 1) {
            throw new \InvalidArgumentException('Invalid message position.');
        }

        $sql = "SELECT 1 FROM messages AS m
                INNER JOIN conversation_members AS cm
                ON cm.conversation_id = m.conversation_id
                AND cm.user_id = :member_id
                WHERE m.conversation_id = :conversation_id
                AND m.message_id < :message_id
                AND m.message_id > cm.cleared_through_message_id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':message_id' => $messageId,
            ':member_id' => $memberId
        ]);

        return $stmt->fetchColumn() !== false;
    }
}

?>