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

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

$cols = [
    'rera_number' => "ALTER TABLE properties ADD COLUMN rera_number VARCHAR(100) NULL DEFAULT NULL AFTER seo_description",
    'rera_expiry' => "ALTER TABLE properties ADD COLUMN rera_expiry DATE NULL DEFAULT NULL AFTER rera_number",
    'featured_rotated_at' => "ALTER TABLE properties ADD COLUMN featured_rotated_at DATETIME NULL DEFAULT NULL AFTER is_featured",
    'removal_flagged_at' => "ALTER TABLE properties ADD COLUMN removal_flagged_at DATETIME NULL DEFAULT NULL AFTER deleted_at",
    'unavailable_reason' => "ALTER TABLE properties ADD COLUMN unavailable_reason VARCHAR(255) NULL DEFAULT NULL AFTER removal_flagged_at",
];
foreach ($cols as $col => $sql) {
    if (!columnExists($pdo, 'properties', $col)) {
        $pdo->exec($sql);
        echo "Added properties.{$col}\n";
    }
}

$tables = [
    'property_price_history' => "CREATE TABLE property_price_history (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        old_min_price DECIMAL(15,2) NULL,
        new_min_price DECIMAL(15,2) NULL,
        old_max_price DECIMAL(15,2) NULL,
        new_max_price DECIMAL(15,2) NULL,
        changed_by INT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY property_id (property_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'property_edit_history' => "CREATE TABLE property_edit_history (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        admin_id INT NULL,
        change_summary VARCHAR(255) NOT NULL,
        before_json LONGTEXT NULL,
        after_json LONGTEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY property_id (property_id),
        KEY created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'admin_login_logs' => "CREATE TABLE admin_login_logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NULL,
        email VARCHAR(190) NOT NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY created_at (created_at),
        KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'admin_sessions' => "CREATE TABLE admin_sessions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        session_id VARCHAR(128) NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        last_seen_at DATETIME NOT NULL,
        revoked_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY session_id (session_id),
        KEY admin_id (admin_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'admin_alerts' => "CREATE TABLE admin_alerts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        alert_type VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        body TEXT NULL,
        entity_type VARCHAR(100) NULL,
        entity_id INT NULL,
        severity ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY is_read (is_read),
        KEY alert_type (alert_type),
        KEY created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $name => $sql) {
    if (!tableExists($pdo, $name)) {
        $pdo->exec($sql);
        echo "Created {$name}\n";
    } else {
        echo "{$name} already exists\n";
    }
}

echo "DONE\n";
