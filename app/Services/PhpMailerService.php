<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Thin wrapper around PHPMailer that mimics what our old Mailable classes
 * did: render a Blade "mail::message" view to HTML and send it over SMTP.
 *
 * This bypasses Laravel's Mail facade / Symfony Mailer transport entirely,
 * so MAIL_MAILER in config/mail.php is no longer used for outgoing mail.
 * Connection settings below are still read from the same .env keys.
 */
class PhpMailerService
{
    /**
     * @param  string  $to
     * @param  string  $subject
     * @param  string  $view       Blade view name, e.g. 'emails.reset-password'
     * @param  array   $data       Data passed to the view
     * @param  string|null  $replyToEmail
     * @param  string|null  $replyToName
     * @return bool
     */
    public function send(
        string $to,
        string $subject,
        string $view,
        array $data = [],
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        $html = View::make($view, $data)->render();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = config('services.phpmailer.host');
            $mail->SMTPAuth = true;
            $mail->Username = config('services.phpmailer.username');
            $mail->Password = config('services.phpmailer.password');
            $mail->SMTPSecure = config('services.phpmailer.encryption', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port = config('services.phpmailer.port', 587);
            $mail->Timeout = config('services.phpmailer.timeout', 10);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $mail->setFrom(
                config('services.phpmailer.from_address'),
                config('services.phpmailer.from_name')
            );
            $mail->addAddress($to);

            if ($replyToEmail) {
                $mail->addReplyTo($replyToEmail, $replyToName ?? '');
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = strip_tags($html);

            $mail->send();

            return true;
        } catch (PHPMailerException $e) {
            Log::error('PHPMailer send failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $mail->ErrorInfo,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
