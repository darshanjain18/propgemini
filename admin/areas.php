<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$cityId = (int) ($_GET['city_id'] ?? 0);
if ($cityId <= 0) {
    echo json_encode(['areas' => []]);
    exit;
}

$pdo = getDatabaseConnection();
$stmt = $pdo->prepare('SELECT id, name FROM areas WHERE city_id = ? ORDER BY name');
$stmt->execute([$cityId]);

echo json_encode(['areas' => $stmt->fetchAll()]);
