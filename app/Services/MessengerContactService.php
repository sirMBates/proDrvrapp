<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class MessengerContactService {
    private UserRepository $userRepository;

    public function __construct(?UserRepository $userRepository = null) {
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function contactsForDriver(int $driverId): array {
        if ($driverId < 1) {
            throw new \InvalidArgumentException('Invalid driver ID.');
        }

        $driver = $this->userRepository->findById($driverId);
        if ($driver['role'] !== 'driver' || $driver['account_status'] !== 'active') {
            throw new \RuntimeException('Messenger requires an active driver account.');
        }

        $users = $this->userRepository->findMessengerContactsForDriver($driverId);
        $contacts = [];

        foreach ($users as $user) {
            $contacts[] = $this->formatIdentity($user);
        }

        usort($contacts, static function (array $a, array $b): int {
            $roleOrder = ['dispatch' => 0, 'driver' => 1];
            $roleComparison = $roleOrder[$a['role']] <=> $roleOrder[$b['role']];

            if ($roleComparison !== 0) {
                return $roleComparison;
            }

            $nameComparison = strnatcasecmp($a['displayName'], $b['displayName']);
            return $nameComparison !== 0 ? $nameComparison : $a['userId'] <=> $b['userId'];
        });

        return $contacts;
    }

    public function formatIdentity(array $user): array {
        $userId = (int) $user['user_id'];
        $role = $user['role'];

        if ($role === 'driver') {
            $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

            $firstName = $user['first_name'] !== '' ? Crypto::decrypt($user['first_name'], $key) : '';
            $lastName = $user['last_name'] !== '' ? Crypto::decrypt($user['last_name'], $key) : '';

            $displayName = trim($firstName . ' ' . $lastName);
            if ($displayName === '') {
                $displayName = 'Driver ' . $userId;
            }

        } elseif ($role === 'dispatch') {
            $displayName = 'Dispatch ' . $userId;

        } elseif ($role === 'admin') {
            $displayName = 'Administration ' . $userId;

        } else {
            throw new \RuntimeException('Unsupported Messenger role.');
        }

        return [
            'userId' => $userId,
            'displayName' => $displayName,
            'role' => $role
        ];
    }
}

?>