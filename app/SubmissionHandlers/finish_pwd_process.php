<?php

declare(strict_types=1);

use App\Services\PasswordResetService;
use App\Validation\Validator;
use App\Validation\UserValidator;
use Core\Flash;

class CompleteResetContr {
    private string $token;
    private string $password;
    private PasswordResetService $passwordResetService;

    public function __construct(string $token, string $password) {
        $this->token = $token;
        $this->password = $password;
        $this->passwordResetService = new PasswordResetService();
    }

    public function completeReset(): void {
        if (!Validator::required($this->password)) {
            Flash::setMsg('warning', 'Please enter your new password.');
            header("Location: /compreset?warning=left+blank");
            exit();
        }

        if (!UserValidator::password($this->password)) {
            Flash::setMsg('warning', 'Please re-type your password.');
            header("Location: /compreset?warning=fix");
            exit();
        }

        $completed = $this->passwordResetService->completeReset($this->token, $this->password);
        if (!$completed) {
            Flash::setMsg('error', 'This password reset is invalid, expired, or has already been used.');
            header("Location: /forget?error=reset+failed");
            exit();
        }
    }

}