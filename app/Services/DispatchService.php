<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class DispatchService {
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function getProfile(int $userId): array {
        if ($userId < 1) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        $user = $this->userRepository->findById($userId);

        if ($user['role'] !== 'dispatch') {
            throw new \RuntimeException('User is not authorized as dispatch.');
        }

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        return [
            'userId' => (int) $user['user_id'],
            'firstName' => Crypto::decrypt($user['first_name'], $key),
            'lastName' => Crypto::decrypt($user['last_name'], $key),
            'staffId' => $user['staff_id'],
            'role' => $user['role'],
            'profilePicture' => $user['profile_picture'] ?: null,
            'accountStatus' => $user['account_status']
        ];
    }
}

?>