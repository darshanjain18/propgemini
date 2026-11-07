<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDatabaseConnection();

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

if (!tableExists($pdo, 'property_views')) {
    $pdo->exec(
        "CREATE TABLE property_views (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            viewer_ip VARCHAR(45) DEFAULT NULL,
            KEY property_id (property_id),
            KEY viewed_at (viewed_at)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    echo "Created property_views\n";
} else {
    echo "property_views already exists\n";
}

// Expand visit status enum with Rescheduled.
$pdo->exec(
    "ALTER TABLE site_visit_requests
     MODIFY status ENUM('Pending','Confirmed','Completed','Cancelled','Rescheduled')
     NOT NULL DEFAULT 'Pending'"
);
echo "Updated site_visit_requests.status enum\n";

if (!columnExists($pdo, 'site_visit_requests', 'reschedule_date')) {
    $pdo->exec('ALTER TABLE site_visit_requests ADD COLUMN reschedule_date DATE NULL DEFAULT NULL AFTER visit_time');
    echo "Added reschedule_date\n";
}
if (!columnExists($pdo, 'site_visit_requests', 'reschedule_time')) {
    $pdo->exec('ALTER TABLE site_visit_requests ADD COLUMN reschedule_time TIME NULL DEFAULT NULL AFTER reschedule_date');
    echo "Added reschedule_time\n";
}

echo "DONE\n";
