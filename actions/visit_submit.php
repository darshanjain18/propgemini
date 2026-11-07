<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/includes/properties.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/automations.php';
require_once dirname(__DIR__) . '/config/database.php';

function jsonOut(bool $ok, string $message, int $status = 200, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge([
        'ok' => $ok,
        'message' => $message,
    ], $extra));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    jsonOut(false, 'Please refresh the page and try again.', 422);
}

// Honeypot: bots that fill this field are rejected quietly.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    jsonOut(true, 'Thank you. Your details have been received.');
}

$code = trim((string) ($_POST['country_code'] ?? '+91'));
$phone = preg_replace('/\s+/', '', trim((string) ($_POST['phone'] ?? ''))) ?? '';
if (!preg_match('/^\d{10}$/', $phone)) {
    jsonOut(false, 'Enter a valid 10-digit contact number.', 422);
}
$_POST['phone'] = $code . ' ' . $phone;
$intent = (string) ($_POST['intent'] ?? 'visit');
$_POST['subject'] = $intent === 'brochure' ? 'Brochure request' : ($intent === 'enquire' ? 'General enquiry' : 'Site visit');

$errors = validateEnquiry($_POST);
if ($errors !== []) {
    jsonOut(false, $errors[0], 422);
}

$propertyId = trim((string) ($_POST['property_id'] ?? ''));
$property = $propertyId !== '' ? propertyById($propertyId) : null;
$when = trim((string) ($_POST['visit_date'] ?? ''));
$time = trim((string) ($_POST['visit_time'] ?? ''));
if ($intent === 'visit') {
    if ($when === '' || $time === '') {
        jsonOut(false, 'Choose a preferred future date and time for your site visit.', 422);
    }

    $visitAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $when . ' ' . $time);
    $errors = DateTimeImmutable::getLastErrors();
    if ($visitAt === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $visitAt <= new DateTimeImmutable('now')) {
        jsonOut(false, 'Choose a valid future date and time for your site visit.', 422);
    }
}
$message = trim((string) $_POST['message']);
$preferredCity = trim((string) ($_POST['preferred_city'] ?? ''));
$propertyType = trim((string) ($_POST['property_type'] ?? ''));
$budgetRange = trim((string) ($_POST['budget_range'] ?? ''));
$contextBits = [];
if ($preferredCity !== '') {
    $contextBits[] = 'Preferred city: ' . $preferredCity;
}
if ($propertyType !== '') {
    $contextBits[] = 'Property type: ' . $propertyType;
}
if ($budgetRange !== '') {
    $contextBits[] = 'Budget: ' . $budgetRange;
}
if ($contextBits !== []) {
    $message = implode('. ', $contextBits) . '. ' . $message;
}
if ($property) {
    $message = 'Property: ' . $property['name'] . ' (' . $property['area'] . ', ' . $property['city'] . '). ' . $message;
}
if ($when !== '') {
    $message = 'Preferred time: ' . $when . ($time !== '' ? ' ' . $time : '') . '. ' . $message;
}
$whatsappConsent = isset($_POST['whatsapp_consent']) ? 1 : 0;

$name = trim((string) $_POST['name']);
$email = trim((string) $_POST['email']);
$fullPhone = trim((string) $_POST['phone']);
$dbPropertyId = $property['db_id'] ?? null;

try {
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO enquiries (name, email, phone, subject, message, whatsapp_consent, source, property_id)
         VALUES (:name, :email, :phone, :subject, :message, :consent, :source, :property_id)'
    );
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $fullPhone,
        'subject' => $_POST['subject'],
        'message' => $message,
        'consent' => $whatsappConsent,
        'source' => $whatsappConsent ? 'visit-form-whatsapp' : 'visit-form',
        'property_id' => $dbPropertyId,
    ]);

    if ($intent === 'visit') {
        $visitStmt = $pdo->prepare(
            'INSERT INTO site_visit_requests
                (name, country_code, contact_number, email, property_id, visit_date, visit_time, message, status)
             VALUES
                (:name, :country_code, :contact_number, :email, :property_id, :visit_date, :visit_time, :message, \'Pending\')'
        );
        $visitStmt->execute([
            'name' => $name,
            'country_code' => $code,
            'contact_number' => $phone,
            'email' => $email,
            'property_id' => $dbPropertyId,
            'visit_date' => $when !== '' ? $when : null,
            'visit_time' => $time !== '' ? $time : null,
            'message' => trim((string) $_POST['message']),
        ]);
    }

    if ($intent === 'brochure' && $dbPropertyId !== null) {
        $brochureStmt = $pdo->prepare(
            'INSERT INTO brochure_requests
                (property_id, name, country_code, contact_number, email, status)
             VALUES
                (:property_id, :name, :country_code, :contact_number, :email, \'New\')'
        );
        $brochureStmt->execute([
            'property_id' => $dbPropertyId,
            'name' => $name,
            'country_code' => $code,
            'contact_number' => $phone,
            'email' => $email,
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Visit form save failed: ' . $e->getMessage());
    jsonOut(false, 'Unable to save your enquiry right now. Please try again later.', 500);
}

if ($property && $intent === 'brochure') {
    $_SESSION['brochure_ok'][$property['id']] = true;
}

$typeLabel = match ($intent) {
    'brochure' => 'Brochure request',
    'enquire' => 'General enquiry',
    default => 'Site visit request',
};

notifyLead([
    'type' => $typeLabel,
    'name' => $name,
    'phone' => $fullPhone,
    'email' => $email,
    'property' => $property
        ? ($property['name'] . ' (' . $property['area'] . ', ' . $property['city'] . ')')
        : '',
    'visit_at' => ($intent === 'visit' && $when !== '')
        ? ($when . ($time !== '' ? ' ' . $time : ''))
        : '',
    'message' => trim((string) $_POST['message']),
    'source' => 'visit-form:' . $intent,
]);

if ($intent === 'visit') {
    createAdminAlert(
        'new_site_visit',
        'New site visit: ' . $name,
        trim(($property ? (string) $property['name'] : 'General') . ' · ' . $when . ' ' . $time),
        'info',
        'site_visit',
        null
    );
} else {
    createAdminAlert(
        'new_enquiry',
        $typeLabel . ': ' . $name,
        substr(trim((string) $_POST['message']), 0, 180),
        'info',
        'enquiry',
        null
    );
}

$extra = [];
if ($property && $intent === 'brochure') {
    require_once dirname(__DIR__) . '/includes/functions.php';
    $extra['brochure_url'] = webPath(
        'actions/brochure.php?id=' . rawurlencode((string) $property['id']) . '&mode=download'
    );
    $extra['property_id'] = (string) $property['id'];
}

jsonOut(true, 'Thank you. Your details have been received.', 200, $extra);
