<?php
// src/Helpers/MailHelper.php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    public static function sendResetLink(string $toEmail, string $toName, string $resetLink): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Hello {$toName},<br><br>
                Click the link below to reset your password. This link expires in 30 minutes.<br><br>
                <a href=\"{$resetLink}\">{$resetLink}</a><br><br>
                If you didn't request this, ignore this email.";
            $mail->AltBody = "Reset your password: {$resetLink}";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail send failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}