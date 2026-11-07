<?php
declare(strict_types=1);

// Simulate authenticated admin and ensure key pages render without fatal errors.

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
$_SERVER['SCRIPT_NAME'] = '/admin/dashboard.php';

require dirname(__DIR__) . '/admin/includes/auth.php';

if (!attemptAdminLogin('admin@propgemini.com', 'ChangeMe@123')) {
    fwrite(STDERR, "LOGIN_FAIL\n");
    exit(1);
}

$pages = [
    'dashboard.php' => 'Dashboard',
    'properties/index.php' => 'Properties',
    'properties/edit.php?id=1' => 'Edit property',
    'enquiries/index.php' => 'Enquiries',
    'visits/index.php' => 'Site visits',
    'settings.php' => 'Settings',
];

foreach ($pages as $path => $needle) {
    $parts = explode('?', $path, 2);
    $_SERVER['SCRIPT_NAME'] = '/admin/' . $parts[0];
    $_GET = [];
    if (isset($parts[1])) {
        parse_str($parts[1], $_GET);
    }

    ob_start();
    include dirname(__DIR__) . '/admin/' . $parts[0];
    $html = ob_get_clean();

    if (!str_contains($html, $needle)) {
        fwrite(STDERR, "FAIL {$path} missing {$needle}\n");
        fwrite(STDERR, substr($html, 0, 400) . PHP_EOL);
        exit(1);
    }
    echo "OK {$path}\n";
}

echo "ALL_ADMIN_PAGES_OK\n";
