<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\PushSubscriptionRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushService {
    private UserRepository $userRepository;
    private PushSubscriptionRepository $subscriptionRepository;

    public function __construct(UserRepository $userRepository, PushSubscriptionRepository $subscriptionRepository) {
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function notifyDriver(int $driverId, int $senderId): array {
        if ($driverId < 1 || $senderId < 1) {
            throw new \InvalidArgumentException('Invalid push notification identity.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            return [
                'accepted' => 0,
                'failed' => 0
            ];
        }

        $subscriptions = $this->subscriptionRepository->findForUser($driverId);

        if ($subscriptions === []) {
            return [
                'accepted' => 0,
                'failed' => 0
            ];
        }

        $subject = $_ENV['WEB_PUSH_SUBJECT'] ?? '';
        $publicKey = $_ENV['WEB_PUSH_PUBLIC_KEY'] ?? '';
        $privateKey = $_ENV['WEB_PUSH_PRIVATE_KEY'] ?? '';

        if ($subject === '' || $publicKey === '' || $privateKey === '') {
            throw new \RuntimeException('Web Push configuration is incomplete.');
        }

        $client = new Client([
            'connect_timeout' => 5,
            'timeout' => 10,
            'allow_redirects' => false
        ]);

        $httpFactory = new HttpFactory();

        $webPush = new WebPush(
            [
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey
                ]
            ],
            [
                'TTL' => 3600,
                'urgency' => 'normal'
            ],
            $client, $httpFactory, $httpFactory);

        $payload = json_encode([
            'sender_id' => $senderId
        ], JSON_THROW_ON_ERROR);

        $accepted = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $browserSubscription = Subscription::create([
                    'endpoint' => $subscription['endpoint'],
                    'keys' => [
                        'p256dh' => $subscription['public_key'],
                        'auth' => $subscription['auth_token']
                    ]
                ]);

                $report = $webPush->sendOneNotification($browserSubscription, $payload);

                if ($report->isSuccess()) {
                    $accepted++;
                    continue;
                }

                $failed++;

                if ($report->isSubscriptionExpired()) {
                    $this->subscriptionRepository->deleteForUser($driverId, $subscription['endpoint']);
                }

                // Avoid logging subscription URLs or key material.
                $status = $report->getResponse()?->getStatusCode();

                error_log('[WEB PUSH] Subscription ' . $subscription['push_subscription_id'] . ' rejected; HTTP ' . ($status ?? 'unavailable'));
            } catch (Throwable $e) {
                $failed++;
                error_log('[WEB PUSH] Subscription ' . $subscription['push_subscription_id'] . ' failed: ' . get_class($e));
            }
        }

        return [
            'accepted' => $accepted,
            'failed' => $failed
        ];
    }
}

?>