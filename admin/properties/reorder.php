<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once dirname(__DIR__) . '/includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

if (!verifyCsrfToken($payload['csrf_token'] ?? null)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid request.']);
    exit;
}

$order = $payload['order'] ?? [];
if (!is_array($order) || $order === []) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Missing order payload.']);
    exit;
}

$ids = [];
foreach ($order as $id) {
    $id = (int) $id;
    if ($id > 0) {
        $ids[] = $id;
    }
}
$ids = array_values(array_unique($ids));
if ($ids === []) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'No valid property ids.']);
    exit;
}

$pdo = getDatabaseConnection();

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL AND id IN ({$placeholders})"
    );
    $check->execute($ids);
    if ((int) $check->fetchColumn() !== count($ids)) {
        throw new RuntimeException('One or more properties were not found.');
    }

    $update = $pdo->prepare('UPDATE properties SET sort_order = ? WHERE id = ? AND deleted_at IS NULL');
    $pdo->beginTransaction();
    foreach ($ids as $index => $id) {
        $update->execute([$index, $id]);
    }
    $pdo->commit();

    logAdminActivity('property_reorder', 'property', null, 'count=' . count($ids));
    echo json_encode(['ok' => true, 'message' => 'Property order saved.']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Property reorder failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not save order.']);
}
