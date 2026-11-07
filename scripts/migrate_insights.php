<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDatabaseConnection();

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

if (!tableExists($pdo, 'site_traffic')) {
    $pdo->exec(
        "CREATE TABLE site_traffic (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            path VARCHAR(255) NOT NULL,
            page_title VARCHAR(255) DEFAULT NULL,
            property_id INT DEFAULT NULL,
            viewer_ip VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            referrer VARCHAR(500) DEFAULT NULL,
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY viewed_at (viewed_at),
            KEY path (path),
            KEY property_id (property_id)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    echo "Created site_traffic\n";
} else {
    echo "site_traffic already exists\n";
}

if (!tableExists($pdo, 'whatsapp_clicks')) {
    $pdo->exec(
        "CREATE TABLE whatsapp_clicks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            source_label VARCHAR(100) NOT NULL DEFAULT 'site',
            property_id INT DEFAULT NULL,
            viewer_ip VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            clicked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY clicked_at (clicked_at),
            KEY source_label (source_label)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    echo "Created whatsapp_clicks\n";
} else {
    echo "whatsapp_clicks already exists\n";
}

echo "DONE\n";
