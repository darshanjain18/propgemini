<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/properties.php';

$id = (string) ($_GET['id'] ?? '');
$mode = (string) ($_GET['mode'] ?? 'view');
$property = propertyById($id);

if ($property === null || empty($_SESSION['brochure_ok'][$id])) {
    http_response_code(403);
    exit('Submit the enquiry form on the property page before opening the brochure.');
}

$brochurePath = trim((string) ($property['brochure_path'] ?? ''));
if ($brochurePath !== '') {
    $absolute = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($brochurePath, '/\\'));
    $real = realpath($absolute);
    $uploadsRoot = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads');
    if ($real !== false && is_file($real) && $uploadsRoot !== false && str_starts_with($real, $uploadsRoot)) {
        $filename = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $property['slug'] ?: $property['id']) . '-brochure.pdf';
        header('Content-Type: application/pdf');
        if ($mode === 'download') {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            header('Content-Disposition: inline; filename="' . $filename . '"');
        }
        header('Content-Length: ' . (string) filesize($real));
        readfile($real);
        exit;
    }
}

$title = $property['name'] . ' brochure';
$body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>'
    . h($title)
    . '</title></head><body style="font-family:Georgia,serif;background:#f7f1e4;color:#241c12;padding:48px"><h1>'
    . h($property['name'])
    . '</h1><p>'
    . h($property['area'] . ', ' . $property['city'] . ', ' . $property['state'])
    . '</p><p>'
    . h($property['summary'])
    . '</p><p>'
    . h($property['bhk'] . ' · ' . $property['size'] . ' · ' . $property['status'])
    . '</p><p>A PDF brochure has not been uploaded for this listing yet. This summary page is temporary access only.</p></body></html>';

header('Content-Type: text/html; charset=UTF-8');
if ($mode === 'download') {
    header('Content-Disposition: attachment; filename="' . ($property['id'] ?? 'property') . '-brochure.html"');
}
echo $body;
