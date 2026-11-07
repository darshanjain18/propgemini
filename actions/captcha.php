<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/properties.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$_SESSION['visit_captcha'] = (string) random_int(1000, 9999);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['code' => $_SESSION['visit_captcha']]);
