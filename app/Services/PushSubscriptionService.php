<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\PushSubscriptionRepository;

class PushSubscriptionService {
    private const ALLOWED_PUSH_HOSTS = [
        'fcm.googleapis.com',
        'updates.push.services.mozilla.com',
        'web.push.apple.com'
    ];

    private UserRepository $userRepository;
    private PushSubscriptionRepository $subscriptionRepository;

    public function __construct(UserRepository $userRepository, PushSubscriptionRepository $subscriptionRepository) {
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function subscribeForDriver(int $driverId, array $data): void {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Push notifications require an active driver account.');
        }

        $endpoint = $this->validateEndpoint($data['endpoint'] ?? null);
        $keys = $data['keys'] ?? null;

        if (!is_array($keys)) {
            throw new \InvalidArgumentException('Push subscription keys are required.');
        }

        $publicKey = $this->validateKey($keys['p256dh'] ?? null, 65, 'public key');
        $authToken = $this->validateKey($keys['auth'] ?? null, 16, 'authentication token');

        $decodedPublicKey = $this->decodeBase64Url($publicKey);

        if (ord($decodedPublicKey[0]) !== 4) {
            throw new \InvalidArgumentException('Invalid push subscription public key.');
        }

        $this->subscriptionRepository->saveForUser($driverId, $endpoint, $publicKey, $authToken);
    }

    private function validateEndpoint(mixed $value): string {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('A push endpoint is required.');
        }

        $endpoint = trim($value);

        if ($endpoint === '' || strlen($endpoint) > 2048 || preg_match('/[\x00-\x20\x7F]/', $endpoint) || filter_var($endpoint, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Invalid push endpoint.');
        }

        $parts = parse_url($endpoint);

        if (!is_array($parts)) {
            throw new \InvalidArgumentException('Unsupported push endpoint.');
        }

        $host = strtolower($parts['host'] ?? '');

        $isAllowedHost = in_array($host, self::ALLOWED_PUSH_HOSTS, true) || str_ends_with($host, '.notify.windows.com');

        if (strtolower($parts['scheme'] ?? '') !== 'https' || !$isAllowedHost || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || (isset($parts['port']) && $parts['port'] !== 443) || empty($parts['path']) || $parts['path'] === '/') {
            throw new \InvalidArgumentException('Unsupported push endpoint.');
        }

        return $endpoint;
    }

    private function validateKey(mixed $value, int $expectedBytes, string $label): string {
        if (!is_string($value) || strlen($value) > 255 || !preg_match('/^[A-Za-z0-9_-]+={0,2}$/D', $value)) {
            throw new \InvalidArgumentException('Invalid push subscription ' . $label . '.');
        }

        $decoded = $this->decodeBase64Url($value);

        if ($decoded === false || strlen($decoded) !== $expectedBytes) {
            throw new \InvalidArgumentException('Invalid push subscription ' . $label . '.');
        }

        // Store a consistent, unpadded Base64URL representation.
        return rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=');
    }

    private function decodeBase64Url(string $value): string|false {
        $unpadded = rtrim($value, '=');
        $padding = (4 - strlen($unpadded) % 4) % 4;

        return base64_decode(strtr($unpadded, '-_', '+/') . str_repeat('=', $padding), true);
    }
}

?>