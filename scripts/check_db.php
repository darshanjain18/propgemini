<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/database.php';

try {
    $pdo = getDatabaseConnection();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "APP_PDO_OK\n";
    echo 'tables=' . count($tables) . "\n";
    echo 'properties=' . (int) $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn() . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'APP_PDO_FAIL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
