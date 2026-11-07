<?php
declare(strict_types=1);

require dirname(__DIR__) . '/admin/includes/auth.php';

$ok = attemptAdminLogin('admin@propgemini.com', 'ChangeMe@123');
echo $ok ? "LOGIN_OK\n" : "LOGIN_FAIL\n";

$user = adminUser();
echo 'user=' . ($user['email'] ?? 'none') . PHP_EOL;

$pdo = getDatabaseConnection();
echo 'properties=' . $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn() . PHP_EOL;
echo 'admin_url_dashboard=' . adminUrl('dashboard.php') . PHP_EOL;
echo 'admin_url_properties=' . adminUrl('properties/index.php') . PHP_EOL;
