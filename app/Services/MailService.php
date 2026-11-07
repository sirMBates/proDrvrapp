<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

class MailService {
    public function createMailer(): PHPMailer {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPAuth = true;

        $mail->Host = (string) $_ENV['MAIL_HOST'];
        $mail->Port = (int) $_ENV['MAIL_PORT'];
        $mail->Username = (string) $_ENV['MAIL_USERNAME'];
        $mail->Password = (string) $_ENV['MAIL_PASSWORD'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->SMTPDebug = 0;

        $mail->setFrom('noreply@prodriver.local', 'ProDriver Help Desk');

        return $mail;
    }

    public function sendPasswordReset(string $email, string $rawToken): void {
        $mail = $this->createMailer();
        $resetUrl = 'https://prodriver.local/reset?token=' . urlencode($rawToken);
        $mail->isHTML(true);
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset';
        $mail->Body = <<<HTML
        <p>You requested a password reset.</p>
        <p>
            <a href="{$resetUrl}">
                Reset your password
            </a>
        </p>
        <p>This link expires in 15 minutes.</p>
        HTML;

        $mail->send();
    }
}

?>
