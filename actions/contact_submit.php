<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/automations.php';
require_once dirname(__DIR__) . '/config/database.php';

function redirectWithStatus(string $status): never
{
    require_once dirname(__DIR__) . '/includes/functions.php';
    $next = trim((string) ($_POST['next'] ?? 'contact'));
    if ($next === 'home') {
        $target = webPath('?status=' . rawurlencode($status)) . '#contact';
    } else {
        $target = webPath('pages/contact?status=' . rawurlencode($status));
    }
    header('Location: ' . $target, true, 303);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    redirectWithStatus('error');
}

// Honeypot: bots that fill this field are rejected quietly.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    redirectWithStatus('success');
}

$errors = validateEnquiry($_POST);

$mobile = preg_replace('/\D/', '', (string) ($_POST['phone'] ?? '')) ?? '';
if (!preg_match('/^\d{10}$/', $mobile)) {
    $errors[] = 'Enter a valid 10-digit contact number.';
}

if ($errors !== []) {
    redirectWithStatus('error');
}

$name = trim((string) $_POST['name']);
$email = trim((string) $_POST['email']);
$phone = trim((string) $_POST['phone']);
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) $_POST['message']);
$whatsappConsent = isset($_POST['whatsapp_consent']) ? 1 : 0;

try {
    $pdo = getDatabaseConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO enquiries
            (name, email, phone, subject, message, whatsapp_consent, source)
         VALUES
            (:name, :email, :phone, :subject, :message, :consent, :source)'
    );

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject !== '' ? $subject : null,
        'message' => $message,
        'consent' => $whatsappConsent,
        'source' => $whatsappConsent ? 'whatsapp-consent' : 'contact',
    ]);

    $typeLabel = $subject !== '' ? $subject : 'Contact form enquiry';
    $source = $whatsappConsent ? 'whatsapp-consent' : 'contact';

    notifyLead([
        'type' => $typeLabel,
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'message' => $message,
        'source' => $source,
        'mail_channel' => 'enquiry',
        'fields' => array_filter([
            'Form type' => $typeLabel,
            'Full name' => $name,
            'Phone' => $phone,
            'Email' => $email,
            'Subject' => $subject,
            'Message' => $message,
            'WhatsApp consent' => $whatsappConsent ? 'Yes' : 'No',
            'Source' => 'website · contact',
            'Submitted at' => date('Y-m-d H:i:s'),
        ], static fn ($value): bool => trim((string) $value) !== ''),
    ]);

    createAdminAlert(
        'new_enquiry',
        'New enquiry: ' . $name,
        substr($message, 0, 180),
        'info',
        'enquiry',
        null
    );

    redirectWithStatus('success');

} catch (Throwable $e) {
    error_log('Enquiry submission failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to submit your enquiry right now. Please try again later.');
}
