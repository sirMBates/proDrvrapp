<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class ProfileAccountService {
    private UserRepository $userRepository;
    private AuthenticationService $authenticationService;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->authenticationService = new AuthenticationService();
    }

    public function updatePassword(int $userId, string $currentPassword, string $newPassword): bool {
        $verified = $this->authenticationService->verifyPasswordByUserId($userId, $currentPassword);
        if (!$verified) {
            return false;
        }

        return $this->userRepository->updatePassword($userId, $newPassword);
    }

    public function emailExistsForOtherUser(int $userId, string $email): bool {
        return $this->userRepository->emailExistsForOtherUser($userId, $email);
    }

    public function updateContactInformation(int $userId, ?string $email, ?string $mobile): bool {
        $encryptedMobile = null;

        if ($mobile !== null && $mobile !== '') {
            $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
            $encryptedMobile = Crypto::encrypt($mobile, $key);
        }

        return $this->userRepository->updateContactInformation($userId, $email, $encryptedMobile);
    }
}

?>