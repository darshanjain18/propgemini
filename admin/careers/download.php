<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/careers.php';

requireAdmin();
ensureCareerSchema();

$id = (int) ($_GET['id'] ?? 0);
$app = careerApplicationById($id);
if ($app === null) {
    http_response_code(404);
    exit('Application not found.');
}

$absolute = careerResumeAbsolutePath((string) ($app['resume_path'] ?? ''));
if ($absolute === null) {
    http_response_code(404);
    exit('Resume file not found.');
}

$original = trim((string) ($app['resume_original_name'] ?? ''));
if ($original === '') {
    $original = 'resume-' . $id . pathinfo($absolute, PATHINFO_EXTENSION);
}

$mime = mime_content_type($absolute) ?: 'application/octet-stream';
$downloadName = preg_replace('/[^\w.\- ()]+/u', '_', $original) ?: ('resume-' . $id);

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($absolute));
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($absolute);
exit;
