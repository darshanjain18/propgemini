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

$propertyId = (int) ($payload['property_id'] ?? 0);
$order = $payload['order'] ?? [];
if ($propertyId <= 0 || !is_array($order) || $order === []) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Missing order payload.']);
    exit;
}

$pdo = getDatabaseConnection();

try {
    $check = $pdo->prepare('SELECT id FROM properties WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $check->execute([$propertyId]);
    if ($check->fetchColumn() === false) {
        throw new RuntimeException('Property not found.');
    }

    $update = $pdo->prepare(
        'UPDATE property_images SET sort_order = ? WHERE id = ? AND property_id = ?'
    );
    $pdo->beginTransaction();
    foreach (array_values($order) as $index => $mediaId) {
        $update->execute([(int) $index, (int) $mediaId, $propertyId]);
    }
    $pdo->commit();

    logAdminActivity('media_reorder', 'property', $propertyId, 'images=' . count($order));
    echo json_encode(['ok' => true, 'message' => 'Gallery order saved.']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Media reorder failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not save order.']);
}
