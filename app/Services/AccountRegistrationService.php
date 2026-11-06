<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Validation\Validator;
use App\Validation\UserValidator;

class AccountRegistrationService {
    private UserRepository $userRepository;
    private AccessKeyService $accessKeyService;

    public function __construct(?UserRepository $userRepository = null, ?AccessKeyService $accessKeyService = null) {
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->accessKeyService = $accessKeyService ?? new AccessKeyService();
    }

    public function createAccount(string $username, string $email, string $password, string $confirmPassword, string $accessKey): array {
        $username = trim($username);
        $email = trim($email);
        $accessKey = trim($accessKey);

        if ($username === '' || $email === '' || $password === '' || $confirmPassword === '' || $accessKey === '') {
            throw new \InvalidArgumentException('Please fill in all required fields.');
        }

        if (!UserValidator::username($username)) {
            throw new \InvalidArgumentException('Please enter a valid username.');
        }

        if (!Validator::email($email)) {
            throw new \InvalidArgumentException('Please enter a valid email address.');
        }

        if (!UserValidator::password($password)) {
            throw new \InvalidArgumentException('Please enter a valid password.');
        }

        if ($password !== $confirmPassword) {
            throw new \InvalidArgumentException('Passwords do not match.');
        }

        if ($this->userRepository->checkUser($username, $email)) {
            throw new \InvalidArgumentException('Username or email already exists.');
        }

        $access = $this->accessKeyService->resolveRegistrationKey($accessKey);

        $role = (string) $access['role'];

        $userId = $this->userRepository->createUser($username, $email, $password, $role, 'pending');

        return [
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'access_key_id' => (int) $access['access_key_id'],
            'cycle_id' => (int) $access['cycle_id']
        ];
    }
}

?>