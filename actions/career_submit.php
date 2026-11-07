<?php
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/careers.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/automations.php';

function careerRedirect(string $status): never
{
    header('Location: ' . webPath('pages/careers?status=' . rawurlencode($status)) . '#apply', true, 303);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    careerRedirect('error');
}

// Honeypot — treat as success to avoid signalling bots.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    careerRedirect('success');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$countryCode = trim((string) ($_POST['country_code'] ?? '+91'));
$phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? '')) ?? '';
$role = trim((string) ($_POST['role'] ?? ''));
$experience = trim((string) ($_POST['experience'] ?? ''));
$notice = trim((string) ($_POST['notice_period'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($countryCode === '' || !preg_match('/^\+\d{1,4}$/', $countryCode)) {
    $countryCode = '+91';
}

$valid = $name !== '' && mb_strlen($name) <= 120
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && mb_strlen($email) <= 190
    && preg_match('/^\d{8,15}$/', $phone) === 1
    && in_array($role, careerRoleOptions(), true)
    && ($experience === '' || in_array($experience, careerExperienceOptions(), true))
    && ($notice === '' || in_array($notice, careerNoticeOptions(), true))
    && mb_strlen($message) <= 5000
    && isset($_FILES['resume'])
    && (int) ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

if (!$valid) {
    careerRedirect('error');
}

if (recentCareerDuplicate($email, $phone, $role)) {
    careerRedirect('duplicate');
}

try {
    $resume = storeCareerResume($_FILES['resume']);
} catch (Throwable $e) {
    error_log('Career resume upload failed: ' . $e->getMessage());
    careerRedirect('error');
}

$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

try {
    $id = createCareerApplication([
        'full_name' => $name,
        'email' => $email,
        'country_code' => $countryCode,
        'phone' => $phone,
        'role' => $role,
        'experience' => $experience !== '' ? $experience : null,
        'notice_period' => $notice !== '' ? $notice : null,
        'message' => $message !== '' ? $message : null,
        'resume_path' => $resume['path'],
        'resume_original_name' => $resume['original'],
        'ip_address' => is_string($ip) ? $ip : null,
        'user_agent' => $ua !== '' ? $ua : null,
    ]);

    $summary = "Role: {$role}\nExperience: "
        . ($experience !== '' ? $experience : '—')
        . "\nNotice: " . ($notice !== '' ? $notice : '—')
        . "\nPhone: {$countryCode} {$phone}\n\n"
        . ($message !== '' ? $message : 'No additional note.');

    notifyLead([
        'type' => 'Career application',
        'name' => $name,
        'phone' => $countryCode . ' ' . $phone,
        'email' => $email,
        'message' => $summary,
        'source' => 'careers',
    ]);

    createAdminAlert(
        'new_enquiry',
        'Career application: ' . $name,
        $role,
        'info',
        'career_application',
        $id
    );

    careerRedirect('success');
} catch (Throwable $e) {
    error_log('Career application failed: ' . $e->getMessage());
    careerRedirect('error');
}
