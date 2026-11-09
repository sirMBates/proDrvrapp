<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Validation\Validator;
use App\Validation\UserValidator;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class ProfileAccountService {
    private UserRepository $userRepository;
    private AuthenticationService $authenticationService;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->authenticationService = new AuthenticationService();
    }
    
    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array {
        if ($userId < 1) {
            return [
                'success' => false,
                'code' => 'unauthorized',
                'message' => 'You must be logged into your account for this change.'
            ];
        }

        if (!Validator::required($currentPassword)) {
            return [
                'success' => false,
                'code' => 'current_password_required',
                'message' => 'Please enter your current password.'
            ];
        }

        if (!Validator::required($newPassword)) {
            return [
                'success' => false,
                'code' => 'new_password_required',
                'message' => 'Please enter your new password.'
            ];
        }

        if (!Validator::required($confirmPassword)) {
            return [
                'success' => false,
                'code' => 'confirm_password_required',
                'message' => 'Please confirm your new password.'
            ];
        }

        if (!UserValidator::password($newPassword)) {
            return [
                'success' => false,
                'code' => 'invalid_password',
                'message' => 'Your new password does not meet the password requirements.'
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'code' => 'passwords_not_match',
                'message' => 'Your new passwords do not match.'
            ];
        }

        if (!$this->updatePassword($userId, $currentPassword, $newPassword)) {
            return [
                'success' => false,
                'code' => 'incorrect_password',
                'message' => 'The current password you entered is incorrect.'
            ];
        }

        return [
            'success' => true,
            'code' => 'password_updated',
            'message' => 'You\'ve successfully updated your password!'
        ];
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
    
    public function changeContactInformation(int $userId, ?string $email, ?string $mobile): array {
        if ($userId < 1) {
            return [
                'success' => false,
                'code' => 'unauthorized',
                'message' => 'You must be logged into your account for this change.'
            ];
        }

        if (!Validator::required($email) && !Validator::required($mobile)) {
            return [
                'success' => false,
                'code' => 'empty',
                'message' => 'Please enter an email address or mobile number.'
            ];
        }

        if (Validator::required($email) && !Validator::email($email)) {
            return [
                'success' => false,
                'code' => 'invalid_email',
                'message' => 'Please enter a valid email address.'
            ];
        }

        if (Validator::required($email) && $this->emailExistsForOtherUser($userId, $email)) {
            return [
                'success' => false,
                'code' => 'email_exists',
                'message' => 'That email address is already in use.'
            ];
        }

        if (Validator::required($mobile) && !UserValidator::mobileNumber($mobile)) {
            return [
                'success' => false,
                'code' => 'invalid_number',
                'message' => 'Please enter a valid mobile number.'
            ];
        }

        $updated = $this->updateContactInformation($userId, $email, $mobile);
        if (!$updated) {
            return [
                'success' => false,
                'code' => 'update_failed',
                'message' => 'The request was not completed. Please try again.'
            ];
        }

        return [
            'success' => true,
            'code' => 'contact_updated',
            'message' => 'Your information has been updated.'
        ];
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