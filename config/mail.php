<?php
declare(strict_types=1);

/**
 * Prefer MAIL_* keys; fall back to SMTP_* so Hostinger-style .env values work.
 */
$host = trim((string) ($_ENV['MAIL_HOST'] ?? $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com'));
$port = (int) ($_ENV['MAIL_PORT'] ?? $_ENV['SMTP_PORT'] ?? 587);
$username = trim((string) ($_ENV['MAIL_USERNAME'] ?? $_ENV['SMTP_USERNAME'] ?? ''));
$password = (string) ($_ENV['MAIL_PASSWORD'] ?? $_ENV['SMTP_PASSWORD'] ?? '');
$encryption = strtolower(trim((string) ($_ENV['MAIL_ENCRYPTION'] ?? $_ENV['SMTP_ENCRYPTION'] ?? 'tls')));
$fromEmail = trim((string) ($_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['SMTP_USERNAME'] ?? ''));
$fromName = trim((string) ($_ENV['MAIL_FROM_NAME'] ?? 'PropGemini Website'));

$enquiryTo = trim((string) (
    $_ENV['MAIL_TO_ENQUIRY']
    ?? $_ENV['MAIL_TO_enquiry']
    ?? 'enquiry@propgemini.com'
));
$careersTo = trim((string) (
    $_ENV['MAIL_TO_CAREERS']
    ?? $_ENV['MAIL_TO_careers']
    ?? 'hr@propgemini.com'
));

// Visible From addresses (display). Prefer channel mailbox over hr@ for enquiry.
$fromEnquiry = trim((string) ($_ENV['MAIL_FROM_ENQUIRY'] ?? $enquiryTo));
$fromCareers = trim((string) ($_ENV['MAIL_FROM_CAREERS'] ?? $careersTo));

return [
    'host' => $host !== '' ? $host : 'smtp.hostinger.com',
    'port' => $port > 0 ? $port : 587,
    'username' => $username,
    'password' => $password,
    'encryption' => in_array($encryption, ['tls', 'ssl', ''], true) ? $encryption : 'tls',
    'from_email' => $fromEmail,
    'from_name' => $fromName !== '' ? $fromName : 'PropGemini Website',
    'from_enquiry' => $fromEnquiry !== '' ? $fromEnquiry : 'enquiry@propgemini.com',
    'from_careers' => $fromCareers !== '' ? $fromCareers : 'hr@propgemini.com',
    'to_enquiry' => $enquiryTo !== '' ? $enquiryTo : 'enquiry@propgemini.com',
    'to_careers' => $careersTo !== '' ? $careersTo : 'hr@propgemini.com',
];
