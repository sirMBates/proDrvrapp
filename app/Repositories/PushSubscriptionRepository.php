<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;
use PDO;

class PushSubscriptionRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new Database())->connect();
    }

    public function saveForUser(int $userId, string $endpoint, string $publicKey, string $authToken): void {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        if ($endpoint === '' || $publicKey === '' || $authToken === '') {
            throw new \InvalidArgumentException('Incomplete push subscription.');
        }

        $endpointHash = hash('sha256', $endpoint);

        $sql = "INSERT INTO push_subscriptions (user_id, endpoint, endpoint_hash, public_key, auth_token)
                VALUES (:user_id, :endpoint, :endpoint_hash, :public_key, :auth_token)
                ON DUPLICATE KEY UPDATE
                    user_id = :updated_user_id,
                    endpoint = :updated_endpoint,
                    public_key = :updated_public_key,
                    auth_token = :updated_auth_token,
                    updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':endpoint' => $endpoint,
            ':endpoint_hash' => $endpointHash,
            ':public_key' => $publicKey,
            ':auth_token' => $authToken,
            ':updated_user_id' => $userId,
            ':updated_endpoint' => $endpoint,
            ':updated_public_key' => $publicKey,
            ':updated_auth_token' => $authToken
        ]);
    }

    public function findForUser(int $userId): array {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        $sql = "SELECT push_subscription_id, user_id, endpoint, public_key, auth_token
                FROM push_subscriptions
                WHERE user_id = :user_id
                ORDER BY push_subscription_id ASC";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        $subscriptions = $stmt->fetchAll();

        foreach ($subscriptions as &$subscription) {
            $subscription['push_subscription_id'] = (int) $subscription['push_subscription_id'];
            $subscription['user_id'] = (int) $subscription['user_id'];
        }

        unset($subscription);
        return $subscriptions;
    }

    public function deleteForUser(int $userId, string $endpoint): void {
        if ($userId < 1 || $endpoint === '') {
            throw new \InvalidArgumentException('Invalid push subscription identity.');
        }

        $sql = "DELETE FROM push_subscriptions
                WHERE user_id = :user_id
                AND endpoint_hash = :endpoint_hash";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':endpoint_hash' => hash('sha256', $endpoint)
        ]);
    }
}

?>