<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class AuthenticationService {
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function authenticate(string $username, string $password): ?array {
        $user = $this->userRepository->findByUsername($username);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return [
                'authenticated' => false,
                'reason' => 'invalid_password'
            ];
        }

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        return [
            'authenticated' => true,
            'userId' => (int) $user['user_id'],
            'firstName' => Crypto::decrypt($user['first_name'], $key),
            'birthdate' => Crypto::decrypt($user['birth_date'], $key)
        ];
    }

    public function verifyPasswordByUserId(int $userId, string $password): bool {
        if ($userId < 1 || $password === '') {
            return false;
        }

        try {
            $user = $this->userRepository->findById($userId);
        } catch (\RuntimeException) {
            return false;
        }
        return password_verify($password, $user['password']);
    }
}

?>