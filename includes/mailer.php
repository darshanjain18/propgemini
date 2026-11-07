<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/config/bootstrap.php';

function sendEmail(
    string $to,
    string $subject,
    string $htmlBody,
    string $plainTextBody = ''
): bool {
    $config = require dirname(__DIR__) . '/config/mail.php';
    if (trim((string) $config['username']) === '' || trim((string) $config['password']) === '' || trim((string) $config['from_email']) === '') {
        error_log('SMTP is not configured (MAIL_USERNAME / MAIL_PASSWORD / MAIL_FROM_ADDRESS). Skipping email.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->Port = $config['port'];

        if ($config['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($config['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainTextBody !== ''
            ? $plainTextBody
            : trim(strip_tags($htmlBody));

        return $mail->send();
    } catch (\Throwable $e) {
        error_log('Email sending failed: ' . $e->getMessage());
        return false;
    }
}
