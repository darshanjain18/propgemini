<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/analytics.php';

$source = trim((string) ($_GET['src'] ?? 'site'));
if ($source === '') {
    $source = 'site';
}
$propertyId = (int) ($_GET['property_id'] ?? 0);

recordWhatsAppClick($source, $propertyId > 0 ? $propertyId : null);

$contact = contactDetails();
if (!$contact['has_whatsapp']) {
    header('Location: ' . webPath('pages/contact'), true, 302);
    exit;
}

$prefill = 'Hi PropGemini';
if ($propertyId > 0) {
    try {
        require_once dirname(__DIR__) . '/includes/properties.php';
        // Look up by DB id for a clearer prefill when available.
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT name FROM properties WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$propertyId]);
        $name = $stmt->fetchColumn();
        if ($name !== false && $name !== '') {
            $prefill = 'Hi PropGemini, I am interested in ' . (string) $name . '.';
        }
    } catch (Throwable $e) {
        // Keep default prefill.
    }
}

header('Location: ' . whatsappChatUrl($contact['whatsapp'], $prefill), true, 302);
exit;
