<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/config/bootstrap.php';

/**
 * Ensure PHP can verify TLS certificates (fixes broken XAMPP relative cafile paths).
 */
function ensureMailCaBundle(): void
{
    $configured = trim((string) (ini_get('openssl.cafile') ?: ini_get('curl.cainfo') ?: ''));
    if ($configured !== '' && is_file($configured)) {
        return;
    }

    $candidates = [
        'D:\\xampp\\apache\\bin\\curl-ca-bundle.crt',
        'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'cacert.pem',
    ];

    foreach ($candidates as $path) {
        if (!is_file($path)) {
            continue;
        }
        @ini_set('curl.cainfo', $path);
        @ini_set('openssl.cafile', $path);
        return;
    }
}

/**
 * @return array{email:string,name:string}
 */
function mailFromFor(string $channel): array
{
    $config = require dirname(__DIR__) . '/config/mail.php';
    $name = (string) $config['from_name'];
    $email = match ($channel) {
        'careers' => (string) $config['from_careers'],
        'enquiry' => (string) $config['from_enquiry'],
        default => (string) $config['from_email'],
    };
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email = (string) $config['from_email'];
    }
    return ['email' => $email, 'name' => $name];
}

/**
 * @param list<array{path:string,name?:string}> $attachments
 */
function sendEmail(
    string $to,
    string $subject,
    string $htmlBody,
    string $plainTextBody = '',
    ?string $replyTo = null,
    array $attachments = [],
    string $channel = 'enquiry'
): bool {
    ensureMailCaBundle();

    $config = require dirname(__DIR__) . '/config/mail.php';
    if (trim((string) $config['username']) === '' || trim((string) $config['password']) === '' || trim((string) $config['from_email']) === '') {
        error_log('SMTP is not configured (MAIL_USERNAME/SMTP_USERNAME, password, from address). Skipping email.');
        return false;
    }

    $to = trim($to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('Invalid mail recipient: ' . $to);
        return false;
    }

    $from = mailFromFor($channel);
    $authEmail = (string) $config['from_email'];

    $attempt = static function (string $fromEmail) use (
        $config,
        $to,
        $subject,
        $htmlBody,
        $plainTextBody,
        $replyTo,
        $attachments,
        $from,
        $authEmail
    ): bool {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $config['username'];
        $mail->Password = (string) $config['password'];
        $mail->Port = (int) $config['port'];

        if ($config['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($config['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Hostname = 'propgemini.com';
        $mail->XMailer = 'PropGemini Website';
        // Return-Path must match authenticated mailbox for SPF alignment.
        $mail->Sender = $authEmail;
        $mail->setFrom($fromEmail, $from['name']);
        $mail->addAddress($to);
        $mail->MessageID = sprintf('<%s@propgemini.com>', bin2hex(random_bytes(12)));
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');
        $mail->addCustomHeader('Auto-Submitted', 'auto-generated');

        $replyTo = $replyTo !== null ? trim($replyTo) : '';
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }

        foreach ($attachments as $attachment) {
            $path = (string) ($attachment['path'] ?? '');
            if ($path === '' || !is_file($path)) {
                continue;
            }
            $name = trim((string) ($attachment['name'] ?? basename($path)));
            $mail->addAttachment($path, $name !== '' ? $name : basename($path));
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainTextBody !== ''
            ? $plainTextBody
            : trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));

        return $mail->send();
    };

    try {
        return $attempt($from['email']);
    } catch (\Throwable $e) {
        // Hostinger may require From = SMTP login; retry with authenticated mailbox.
        if (strcasecmp($from['email'], $authEmail) !== 0) {
            try {
                error_log('Email From override failed (' . $e->getMessage() . '); retrying with auth mailbox.');
                return $attempt($authEmail);
            } catch (\Throwable $retry) {
                error_log('Email sending failed: ' . $retry->getMessage());
                return false;
            }
        }
        error_log('Email sending failed: ' . $e->getMessage());
        return false;
    }
}

function mailRecipientFor(string $channel): string
{
    $config = require dirname(__DIR__) . '/config/mail.php';
    return match ($channel) {
        'careers' => (string) $config['to_careers'],
        default => (string) $config['to_enquiry'],
    };
}
