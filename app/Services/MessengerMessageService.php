<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;
use App\Sanitization\Sanitizer;
use App\Validation\Validator;

class MessengerMessageService {
    private const MAX_MESSAGE_LENGTH = 2000;

    public function __construct(private UserRepository $userRepository, private MessageRepository $messageRepository) {}

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

        return $this->messageRepository->send((int) $conversationId, $driverId, strtolower($clientMessageId), $messageBody);
    }

    public function getForDriver(int $driverId, int $conversationId, int $afterMessageId = 0, int $limit = 50): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        if ($conversationId < 1) {
            throw new \InvalidArgumentException('Invalid conversation.');
        }

        if ($afterMessageId < 0) {
            throw new \InvalidArgumentException('Invalid message cursor.');
        }

        $messages = $this->messageRepository->findForMember($conversationId, $driverId, $afterMessageId, $limit);
        $latestMessageId = $afterMessageId;

        if ($messages !== []) {
            $lastMessage = $messages[
                array_key_last($messages)
            ];

            $latestMessageId = (int) $lastMessage['message_id'];
        }

        return [
            'conversation_id' => $conversationId,
            'latest_message_id' => $latestMessageId,
            'message_count' => count($messages),
            'messages' => $messages
        ];
    }
}

?>