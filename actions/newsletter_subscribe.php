<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/news.php';

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Invalid request.']);
    exit;
}

$email = (string) ($_POST['email'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$result = subscribeNewsletter($email, 'news_page', is_string($ip) ? $ip : null);
echo json_encode($result);
exit;
