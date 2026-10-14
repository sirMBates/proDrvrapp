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

        if ($recipient['account_status'] !== 'active') {
            throw new \DomainException('This account is unavailable for messaging.');
        }

        if ($recipient['role'] === 'admin') {
            $conversation = $this->conversationRepository->findAdminConversationForDriver($driverId, $recipientId);
            if ($conversation === null) {
                throw new \DomainException('Administration must contact you before you can reply.');
            }

            return $conversation;
        }

        if (!in_array($recipient['role'], ['driver', 'dispatch'], true)) {
            throw new \DomainException('You cannot start a conversation with this account.');
        }

        return $this->conversationRepository->findOrCreateDirect($driverId, $recipientId);
    }

    public function unreadForDriver(int $driverId): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $summaries = $this->conversationRepository->findUnreadSummariesForMember($driverId);
        $otherMembers = $this->conversationRepository->findOtherMembersForMember($driverId);
        $recipientsByConversation = [];

        $identitiesByConversation = [];
        $contactService = new MessengerContactService($this->userRepository);

        foreach ($otherMembers as $member) {
            $conversationId = (int) $member['conversation_id'];
            $recipientsByConversation[$conversationId][] = (int) $member['user_id'];
            $identitiesByConversation[$conversationId][] = $contactService->formatIdentity($member);
        }

        $totalUnread = 0;

        foreach ($summaries as &$summary) {
            $conversationId = $summary['conversation_id'];
            $recipientIds = $recipientsByConversation[$conversationId] ?? [];

            // Only a two-member direct conversation maps to one contact.
            $summary['recipient_id'] = $summary['conversation_type'] === 'direct' && count($recipientIds) === 1 ? $recipientIds[0] : null;
            $summary['recipient'] = $summary['recipient_id'] !== null ? $identitiesByConversation[$conversationId][0] : null;
            $summary['latest_is_own'] = $summary['latest_sender_id'] !== null && $summary['latest_sender_id'] === $driverId;
            $totalUnread += $summary['unread_count'];
        }

        unset($summary);

        return [
            'total_unread' => $totalUnread,
            'conversations' => $summaries
        ];
    }

    public function markReadForDriver(int $driverId, int $conversationId, int $messageId): array {
        if ($driverId < 1 || $conversationId < 1 || $messageId < 1) {
            throw new \InvalidArgumentException('Invalid read position.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $readPosition = $this->conversationRepository->markReadForMember($conversationId, $driverId, $messageId);

        return [
            'conversation_id' => $conversationId,
            'last_read_message_id' => $readPosition
        ];
    }

    public function deleteForDriver(int $driverId, array $conversationIds): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        return [
            'deleted_conversations' =>  $this->conversationRepository->deleteForMember($driverId, $conversationIds)
        ];
    }
}

?>