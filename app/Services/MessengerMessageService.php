<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Sanitization\Sanitizer;
use App\Validation\Validator;

class MessengerMessageService {
    private const MAX_MESSAGE_LENGTH = 2000;

    public function __construct(private UserRepository $userRepository, private MessageRepository $messageRepository, private ?WebPushService $webPushService = null) {}

    public function sendForDriver(int $driverId, array $data): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $conversationId = filter_var($data['conversation_id'] ?? null, FILTER_VALIDATE_INT);
        if ($conversationId === false || $conversationId < 1) {
            throw new \InvalidArgumentException('Invalid conversation.');
        }

        $clientMessageId = trim((string) ($data['client_message_id'] ?? ''));
        if (!Validator::uuid($clientMessageId)) {
            throw new \InvalidArgumentException('Invalid client message ID.');
        }

        $messageBody = Sanitizer::plainText((string) ($data['message_body'] ?? ''));
        if (!Validator::textLength($messageBody, 1, self::MAX_MESSAGE_LENGTH)) {
            throw new \InvalidArgumentException('Your message must contain between 1 and 2,000 characters.');
        }

        $message = $this->messageRepository->send((int) $conversationId, $driverId, strtolower($clientMessageId), $messageBody);

        // The repository has committed successfully before reaching this point.
        if ($message['is_new'] && $this->webPushService !== null) {
            try {
                $recipientIds = $this->messageRepository->findRecipientIdsForMessage($message['message_id']);

                foreach ($recipientIds as $recipientId) {
                    try {
                        $this->webPushService->notifyDriver($recipientId, $driverId);
                    } catch (\Throwable $e) {
                        error_log('[MESSENGER PUSH] Message ' . $message['message_id'] . ', recipient ' . $recipientId . ': ' . get_class($e));
                    }
                }
            } catch (\Throwable $e) {
                error_log('[MESSENGER PUSH] Recipient lookup failed for message ' . $message['message_id'] . ': ' . get_class($e));
            }
        }

        unset($message['is_new']);
        return $message;
    }

    public function getForDriver(int $driverId, int $conversationId, int $afterMessageId = 0, int $limit = 50, int $beforeMessageId = 0): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        if ($conversationId < 1) {
            throw new \InvalidArgumentException('Invalid conversation.');
        }

        if ($afterMessageId < 0 || $beforeMessageId < 0) {
            throw new \InvalidArgumentException('Invalid message cursor.');
        }

        if ($afterMessageId > 0 && $beforeMessageId > 0) {
            throw new \InvalidArgumentException('Use either an after cursor or a before cursor.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $messages = $this->messageRepository->findForMember($conversationId, $driverId, $afterMessageId, $limit, $beforeMessageId);

        foreach ($messages as &$message) {
            $message['is_own'] = $message['sender_id'] === $driverId;
        }

        unset($message);

        $latestMessageId = $afterMessageId;
        $oldestMessageId = null;
        $hasOlderMessages = false;

        if ($messages !== []) {
            $oldestMessageId = (int) $messages[0]['message_id'];

            $latestMessageId = (int) $messages[array_key_last($messages)]['message_id'];

            // Polling does not need an older-history existence check.
            if ($afterMessageId === 0) {
                $hasOlderMessages = $this->messageRepository->hasMessagesBefore($conversationId, $oldestMessageId, $driverId);
            }
        }

        return [
            'conversation_id' => $conversationId,
            'latest_message_id' => $latestMessageId,
            'oldest_message_id' => $oldestMessageId,
            'has_older_messages' => $hasOlderMessages,
            'message_count' => count($messages),
            'messages' => $messages
        ];
    }
}

?>