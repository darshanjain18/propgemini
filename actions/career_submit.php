<?php
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/automations.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/admin/includes/upload.php';

function careerRedirect(string $status): never
{
    header('Location: ' . webPath('pages/careers.php?status=' . rawurlencode($status)), true, 303);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    careerRedirect('error');
}

if (trim((string) ($_POST['website'] ?? '')) !== '') {
    careerRedirect('success');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? '')) ?? '';
$role = trim((string) ($_POST['role'] ?? ''));
$experience = trim((string) ($_POST['experience'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$allowedExperience = ['0-2', '2-4', '4-6', '6+'];

$valid = $name !== '' && mb_strlen($name) <= 100
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && preg_match('/^\d{10}$/', $phone) === 1
    && in_array($experience, $allowedExperience, true)
    && $role !== '' && mb_strlen($role) <= 120
    && $message !== '' && mb_strlen($message) <= 5000
    && isset($_FILES['resume'])
    && (int) ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

if (!$valid) {
    careerRedirect('error');
}

try {
    $resumePath = storeUploadedFile(
        $_FILES['resume'],
        'resumes',
        [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        8 * 1024 * 1024
    );
} catch (Throwable $e) {
    careerRedirect('error');
}

$body = "Experience: {$experience} years\nRole: {$role}\nRésumé: " . webPath($resumePath) . "\n\n" . $message;

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO enquiries (name, email, phone, subject, message, whatsapp_consent, source)
         VALUES (:name, :email, :phone, :subject, :message, 0, :source)'
    );
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => 'Career application: ' . $role,
        'message' => $body,
        'source' => 'careers',
    ]);

    notifyLead([
        'type' => 'Career application',
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'message' => $body,
        'source' => 'careers',
    ]);

    createAdminAlert(
        'new_enquiry',
        'Career application: ' . $name,
        $role,
        'info',
        'enquiry',
        null
    );

    careerRedirect('success');
} catch (Throwable $e) {
    error_log('Career application failed: ' . $e->getMessage());
    careerRedirect('error');
}
