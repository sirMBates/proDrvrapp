<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\ConversationRepository;

class MessengerConversationService {
    private UserRepository $userRepository;
    private ConversationRepository $conversationRepository;

    public function __construct(UserRepository $userRepository, ConversationRepository $conversationRepository) {
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
    }

    public function openForDriver(int $driverId, int $recipientId): array {
        if ($driverId < 1 || $recipientId < 1 || $driverId === $recipientId) {
            throw new \InvalidArgumentException('Choose another user to message.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $recipient = $this->userRepository->findById($recipientId);
        if ($recipient['account_status'] !== 'active' || !in_array($recipient['role'], ['driver', 'dispatch'], true)) {
            throw new \DomainException('You cannot start a conversation with this account.');
        }

        return $this->conversationRepository->findOrCreateDirect($driverId, $recipientId);
    }
}

?>