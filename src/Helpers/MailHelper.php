<?php
// src/Helpers/MailHelper.php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {

    public static function sendResetLink(string $toEmail, string $toName, string $resetLink, int $expiryMinutes = 30): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $_ENV['MAIL_PORT'];
            $mail->CharSet    = 'UTF-8';

            $fromName = $_ENV['MAIL_FROM_NAME'] ?? 'HRM System';
            $mail->setFrom($_ENV['MAIL_FROM'], $fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'የይለፍ ቃል መቀየሪያ | Password Reset';
            $mail->Body    = self::buildResetEmailHtml($toName, $resetLink, $expiryMinutes, $fromName);
            $mail->AltBody = self::buildResetEmailText($toName, $resetLink, $expiryMinutes);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail send failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    private static function buildResetEmailHtml(string $name, string $link, int $minutes, string $systemName): string {
        $name       = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink   = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $systemName = htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8');
        $year       = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="am">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Reset</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f7fb;">

<!-- Hidden preview text shown in the inbox list -->
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
    የይለፍ ቃልዎን ለመቀየር ማስፈንጠሪያው ለ{$minutes} ደቂቃ ያገለግላል።
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f7fb;padding:32px 12px;">
<tr>
<td align="center">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="max-width:560px;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">

        <!-- Header -->
        <tr>
            <td align="center" style="background-color:#0d6efd;padding:26px 24px;">
                <div style="font-family:'Noto Sans Ethiopic','Nyala','Segoe UI',Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;">
                    {$systemName}
                </div>
                <div style="font-family:'Segoe UI',Arial,sans-serif;font-size:13px;color:#dbe8ff;margin-top:4px;">
                    Human Resource Management
                </div>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:34px 32px 8px 32px;font-family:'Noto Sans Ethiopic','Nyala','Segoe UI',Arial,sans-serif;color:#1f2937;">

                <h1 style="margin:0 0 6px 0;font-size:20px;font-weight:700;color:#172033;">
                    የይለፍ ቃል መቀየሪያ
                </h1>
                <p style="margin:0 0 22px 0;font-size:13px;color:#6b7280;">Password reset request</p>

                <p style="margin:0 0 14px 0;font-size:15px;line-height:1.7;">
                    ሰላም <strong>{$name}</strong>፣
                </p>

                <p style="margin:0 0 24px 0;font-size:15px;line-height:1.7;">
                    የመለያዎን የይለፍ ቃል ለመቀየር ጥያቄ ደርሶናል። አዲስ የይለፍ ቃል ለመፍጠር ከታች ያለውን ቁልፍ ይጫኑ።
                </p>

                <!-- Button -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 26px auto;">
                    <tr>
                        <td align="center" bgcolor="#0d6efd" style="border-radius:8px;">
                            <a href="{$safeLink}" target="_blank"
                               style="display:inline-block;padding:14px 34px;font-family:'Noto Sans Ethiopic','Nyala','Segoe UI',Arial,sans-serif;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                                የይለፍ ቃል ቀይር
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Expiry notice -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:22px;">
                    <tr>
                        <td style="background-color:#fff8e6;border-left:3px solid #f59e0b;padding:12px 14px;border-radius:6px;font-size:13px;line-height:1.6;color:#92400e;">
                            ይህ ማስፈንጠሪያ ለ <strong>{$minutes} ደቂቃ</strong> ብቻ የሚያገለግል ሲሆን አንድ ጊዜ ብቻ መጠቀም ይቻላል።
                            <br><span style="font-size:12px;">This link expires in {$minutes} minutes and can be used only once.</span>
                        </td>
                    </tr>
                </table>

                <p style="margin:0 0 6px 0;font-size:13px;color:#6b7280;line-height:1.6;">
                    ቁልፉ ካልሰራ ይህን አድራሻ ይጠቀሙ፦
                </p>
                <p style="margin:0 0 24px 0;font-size:12px;line-height:1.6;word-break:break-all;">
                    <a href="{$safeLink}" style="color:#0d6efd;text-decoration:underline;">{$safeLink}</a>
                </p>

                <hr style="border:0;border-top:1px solid #e5e7eb;margin:0 0 18px 0;">

                <p style="margin:0 0 22px 0;font-size:13px;color:#6b7280;line-height:1.7;">
                    ይህን ጥያቄ ካላቀረቡ ይህን ኢሜይል አይክፈቱት፤ የይለፍ ቃልዎ አይቀየርም።
                    <br><span style="font-size:12px;">If you didn't request this, you can safely ignore this email. Your password will not change.</span>
                </p>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td align="center" style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:18px 24px;font-family:'Noto Sans Ethiopic','Nyala','Segoe UI',Arial,sans-serif;font-size:12px;color:#9ca3af;line-height:1.7;">
                ይህ አውቶማቲክ መልዕክት ነው፤ እባክዎ ምላሽ አይስጡ።<br>
                This is an automated message. Please do not reply.<br>
                &copy; {$year} {$systemName}
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>
HTML;
    }

    private static function buildResetEmailText(string $name, string $link, int $minutes): string {
        return "ሰላም {$name}፣\n\n"
             . "የይለፍ ቃልዎን ለመቀየር ይህን አድራሻ ይክፈቱ (ለ{$minutes} ደቂቃ ብቻ ያገለግላል)፦\n"
             . "{$link}\n\n"
             . "ይህን ጥያቄ ካላቀረቡ ይህን ኢሜይል ችላ ይበሉ።\n\n"
             . "Hello {$name},\n"
             . "Open the link above to reset your password (valid for {$minutes} minutes).\n"
             . "If you didn't request this, ignore this email.\n\n"
             . "This is an automated message. Please do not reply.";
    }
}