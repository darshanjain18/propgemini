<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
);
$stmt->execute(['properties', 'sort_order']);
$exists = (int) $stmt->fetchColumn() > 0;

if (!$exists) {
    $pdo->exec(
        'ALTER TABLE properties ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_active'
    );
    echo "Added properties.sort_order\n";
} else {
    echo "properties.sort_order already exists\n";
}

$pdo->exec('UPDATE properties SET sort_order = id WHERE sort_order = 0');
echo "Backfilled sort_order from id where needed\n";
