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

    public function createGroupForDriver(int $driverId, array $data): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if (!$driver || $driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $title = $data['title'] ?? null;
        if (!is_string($title)) {
            throw new \InvalidArgumentException('Enter a group name.');
        }

        $title = trim($title);
        if ($title === '' || !mb_check_encoding($title, 'UTF-8') || mb_strlen($title, 'UTF-8') > 150) {
            throw new \InvalidArgumentException('Enter a group name between 1 and 150 characters.');
        }

        $recipientIds = $data['recipient_ids'] ?? null;
        if (!is_array($recipientIds) || !array_is_list($recipientIds)) {
            throw new \InvalidArgumentException('Choose the contacts for this group.');
        }

        // The creator is included automatically by the repository.
        if (count($recipientIds) > 49) {
            throw new \InvalidArgumentException('Choose no more than 49 other group members.');
        }

        foreach ($recipientIds as $recipientId) {
            if (!is_int($recipientId) || $recipientId < 1) {
                throw new \InvalidArgumentException('Invalid group member.');
            }
        }

        $recipientIds = array_values(array_unique(array_filter($recipientIds, static fn (int $recipientId): bool => $recipientId !== $driverId)));

        if (count($recipientIds) < 2) {
            throw new \InvalidArgumentException('Choose at least two other contacts for this group.');
        }

        return $this->conversationRepository->createGroup($driverId, $title, $recipientIds);
    }

    public function unreadForDriver(int $driverId): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if (!$driver || $driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $summaries = $this->conversationRepository->findUnreadSummariesForMember($driverId);
        $otherMembers = $this->conversationRepository->findOtherMembersForMember($driverId);

        $identitiesByConversation = [];
        $contactService = new MessengerContactService($this->userRepository);

        foreach ($otherMembers as $member) {
            $conversationId = (int) $member['conversation_id'];
            $identitiesByConversation[$conversationId][] = $contactService->formatIdentity($member);
        }

        $totalUnread = 0;

        foreach ($summaries as &$summary) {
            $conversationId = (int) $summary['conversation_id'];
            $members = $identitiesByConversation[$conversationId] ?? [];

            $isDirect = $summary['conversation_type'] === 'direct';
            $isGroup = $summary['conversation_type'] === 'group';

            // Preserve the existing direct-conversation response.
            $recipient = $isDirect && count($members) === 1 ? $members[0] : null;

            $summary['recipient_id'] = $recipient !== null ? (int) $recipient['userId'] : null;
            $summary['recipient'] = $recipient;
            $summary['member_count'] = count($members) + 1;
            $summary['display_name'] = $isGroup ? (trim((string) ($summary['title'] ?? '')) ?: 'Group conversation') : ($recipient['displayName'] ?? 'Conversation');
            $summary['latest_is_own'] = $summary['latest_sender_id'] !== null && (int) $summary['latest_sender_id'] === $driverId;
            $totalUnread += (int) $summary['unread_count'];
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

    public function groupMembersForDriver(int $driverId, int $conversationId): array {
        if ($driverId < 1 || $conversationId < 1) {
            throw new \InvalidArgumentException('Invalid Messenger identity.');
        }

        $driver = $this->userRepository->findById($driverId);

        if (!$driver || $driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \DomainException('Messenger requires an active driver account.');
        }

        $rows = $this->conversationRepository->findGroupMembersForMember($conversationId, $driverId);

        $contactService = new MessengerContactService($this->userRepository);
        $members = [];

        foreach ($rows as $row) {
            $identity = $contactService->formatIdentity($row);

            $identity['is_self'] = $row['user_id'] === $driverId;
            $identity['is_creator'] = $row['user_id'] === $row['created_by'];

            $members[] = $identity;
        }

        usort($members, static function (array $a, array $b): int {
            // Show the creator first, then sort by display name.
            if ($a['is_creator'] !== $b['is_creator']) {
                return $a['is_creator'] ? -1 : 1;
            }

            return strnatcasecmp($a['displayName'], $b['displayName']) ?: $a['userId'] <=> $b['userId'];
        });

        return [
            'conversation_id' => $conversationId,
            'member_count' => count($members),
            'members' => $members
        ];
    }
}

?>