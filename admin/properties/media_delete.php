<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('properties/index.php');
}

$propertyId = (int) ($_POST['property_id'] ?? 0);
$mediaId = (int) ($_POST['media_id'] ?? 0);
$type = (string) ($_POST['type'] ?? 'image');

if ($propertyId <= 0 || $mediaId <= 0) {
    adminFlash('error', 'Missing media details.');
    adminRedirect('properties/index.php');
}

$pdo = getDatabaseConnection();

try {
    if ($type === 'image') {
        $stmt = $pdo->prepare('DELETE FROM property_images WHERE id = ? AND property_id = ?');
        $stmt->execute([$mediaId, $propertyId]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM property_videos WHERE id = ? AND property_id = ?');
        $stmt->execute([$mediaId, $propertyId]);
    }
    logAdminActivity('media_delete', 'property', $propertyId, $type . ':' . $mediaId);
    adminFlash('success', 'Media removed.');
} catch (Throwable $e) {
    error_log('Media delete failed: ' . $e->getMessage());
    adminFlash('error', 'Could not remove media.');
}

adminRedirect('properties/edit.php?id=' . $propertyId);
