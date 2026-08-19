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

    public function resetRequest(): ?array {
        if (!$this->hasEmail()) {
            Flash::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /forget?warning=empty"); //emptyinput
            exit();
        }

        if (!$this->inValidEmail()) {
            Flash::setMsg('warning', 'Please re-enter your email.');
            header("Location: /forget?warning=invalid"); //emailnotvalid
            exit();
        }
        
        return $this->passwordResetService->createResetRequest($this->email);
    }

    public function sendForgetEmail() {
        if ($this->emailExistandSend() === false) {
            $alert::setMsg('error', 'Email not sent. Please try again.');
            header('Location: /forget?error=try+again');
            exit();
        } else {
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
        }
    }

    private function hasEmail(): bool {
        return Validator::required($this->email);
    }

    private function invalidEmail() {
        return Validator::email($this->email);
    }
}

?>