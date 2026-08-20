<?php

declare(strict_types=1);

use App\Services\PasswordResetService;
use App\Validation\Validator;
use Core\Flash;

class ForgetPswdContr {
    private string $email;
    private PasswordResetService $passwordResetService;

    public function __construct(string $email) {
        $this->email = $email;
        $this->passwordResetService = new PasswordResetService();
    }

    public function requestReset(): ?array {
        if (!$this->hasEmail()) {
            Flash::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /forget?warning=empty"); //emptyinput
            exit();
        }

        if (!$this->isValidEmail()) {
            Flash::setMsg('warning', 'Please re-enter your email.');
            header("Location: /forget?warning=invalid"); //emailnotvalid
            exit();
        }
        
        $resetRequest = $this->passwordResetService->createResetRequest($this->email);

        if ($resetRequest !== null) {
            $this->sendForgetEmail($resetRequest);
        }

        return $resetRequest;
    }

    private function sendForgetEmail(array $resetRequest): void {
        $mail = require_once base_path("core/emailSetup.php");
        $rawToken = $resetRequest['rawToken'];
        $email = $resetRequest['email'];        
        $resetUrl = "https://prodriver.local/reset?token=" . urlencode($rawToken);

        $mail->isHTML(true);
        $mail->setFrom('noreply@prodriver.local', 'Help Desk');
        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        $mail->Body = <<<HTML
        <p>You requested a password reset.</p>
        <p>
            <a href="{$resetUrl}">
                Reset your password
            </a>
        </p>
        <p>This link expires in 15 minutes.</p>
        HTML;

        try {
            $mail->send();
        } catch (\Throwable $exception) {
            Flash::setMsg('danger', "Message could not be sent. Please try again.");
            header("Location: /forget?danger=system+error");
            exit();
        }
    }

    private function hasEmail(): bool {
        return Validator::required($this->email);
    }

    private function isValidEmail(): bool {
        return Validator::email($this->email);
    }
}

?>