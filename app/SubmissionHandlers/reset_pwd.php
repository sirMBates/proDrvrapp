<?php

declare(strict_types=1);

use App\Services\PasswordResetService;
use Core\Flash;

class ResetPwdContr {
    private string $token;
    private PasswordResetService $passwordResetService;

    public function __construct($token) {
        $this->token = $token;
        $this->passwordResetService = new PasswordResetService();
    }

    public function validateResetToken(): array {
        $resetRequest = $this->passwordResetService->validateToken($this->token);

        if ($resetRequest === null) {
            Flash::setMsg('error', 'This password reset link is invalid or has expired.');
            header("Location: /forget?error=invalid");
            exit();
        }

        return $resetRequest;
    }
}

?>