<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Validation\Validator;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class AuthenticationService {
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function authenticate(string $username, string $password): array {
        $username = trim($username);

        if (!Validator::required($username) || !Validator::required($password)) {
            throw new \InvalidArgumentException('Please fill in all fields.');
        }

        $user = $this->userRepository->findByUsername($username);
        if ($user === null || !password_verify($password, $user['password'])) {
            throw new \InvalidArgumentException('Invalid username or password.');
        }

        $status = $user['account_status'] ?? null;
        if ($status === 'pending') {
            return [
                'authenticated' => false,
                'registrationPending' => true,
                'userId' => (int) $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
        }

        if ($status !== 'active') {
            $message = match ($status) {
                'locked' => 'Your account has been locked by administration. Please contact your administrator.',
                'inactive-locked' => 'Your account has been suspended due to inactivity. Please contact your administrator.',
                default => 'Your account is currently unavailable. Please contact your administrator.'
            };

            throw new \DomainException($message);
        }

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        return [
            'authenticated' => true,
            'userId' => (int) $user['user_id'],
            'firstName' => Crypto::decrypt($user['first_name'], $key),
            'birthdate' => $user['role'] === 'driver' && !empty($user['birth_date']) ? Crypto::decrypt($user['birth_date'], $key) : null,
            'role' => $user['role']
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

        return $user !== null && password_verify($password, $user['password']);
    }
}

?>