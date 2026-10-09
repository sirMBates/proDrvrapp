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
}

?>