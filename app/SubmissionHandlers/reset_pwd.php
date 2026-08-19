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

/**else {
            $mail = require_once base_path("core/emailSetup.php");
            $mail->setFrom('noreply@prodriver.local', 'Help Desk');
            $mail->addAddress($this->email);
            $mail->Subject = "Forget Password";
            $mail->Body = <<<END

                    Click <a href="https://prodriver.local/reset?token=$this->token">here</a> to reset password.

                    END;
            try {
                $mail->send();
            } catch (Exception $e) {
                //remove(comment out if need to check mailer errors).
                //echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
                $alert::setMsg('danger', "Message not sent. Try again. {$mail->ErrorInfo}");
                header("Location: /forget?danger=system+error");
                exit();
            }
        } */

?>