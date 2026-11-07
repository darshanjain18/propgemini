<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('properties/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    adminFlash('error', 'Property not found.');
    adminRedirect('properties/index.php');
}

$pdo = getDatabaseConnection();

try {
    $stmt = $pdo->prepare('SELECT name FROM properties WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$id]);
    $name = $stmt->fetchColumn();
    if ($name === false) {
        throw new RuntimeException('Property not found.');
    }

    // Soft delete: keep the row and related media, hide from public + admin lists.
    $pdo->prepare(
        "UPDATE properties
         SET is_active = 0,
             publication_status = 'Unpublished',
             is_featured = 0,
             deleted_at = NOW()
         WHERE id = ?"
    )->execute([$id]);

    logAdminActivity('property_soft_delete', 'property', $id, (string) $name);
    adminFlash('success', 'Property removed from the site (soft deleted).');
} catch (Throwable $e) {
    error_log('Property soft delete failed: ' . $e->getMessage());
    adminFlash('error', 'Could not delete property.');
}

adminRedirect('properties/index.php');
