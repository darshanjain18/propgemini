<?php
declare(strict_types=1);

/**
 * Phase 5 deploy / handoff smoke check.
 * Run from project root: php scripts/smoke_handoff.php
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/settings.php';

$root = dirname(__DIR__);
$failures = 0;
$warnings = 0;

function smoke(string $label, bool $ok, string $detail = '', bool $optional = false): void
{
    global $failures, $warnings;
    if (!$ok) {
        if ($optional) {
            $warnings++;
        } else {
            $failures++;
        }
    }
    if ($ok) {
        echo '[OK]   ' . $label;
    } elseif ($optional) {
        echo '[WARN] ' . $label;
    } else {
        echo '[FAIL] ' . $label;
    }
    if ($detail !== '') {
        echo ' — ' . $detail;
    }
    echo PHP_EOL;
}

echo "PropGemini handoff smoke check\n";
echo str_repeat('-', 40) . PHP_EOL;
echo 'PHP ' . PHP_VERSION . PHP_EOL;

smoke('PHP 8.1+', version_compare(PHP_VERSION, '8.1.0', '>='));
smoke('vendor/autoload.php', is_file($root . '/vendor/autoload.php'));
smoke(
    'GD extension (image resize)',
    extension_loaded('gd'),
    extension_loaded('gd') ? 'enabled' : 'optional but recommended',
    true
);

$uploads = $root . '/public/uploads';
smoke('public/uploads exists', is_dir($uploads));
smoke('public/uploads writable', is_dir($uploads) && is_writable($uploads));

try {
    $pdo = getDatabaseConnection();
    smoke('Database connection', true);

    $tables = [
        'properties',
        'property_images',
        'admins',
        'site_settings',
        'enquiries',
        'site_visit_requests',
        'admin_activity_logs',
    ];
    foreach ($tables as $table) {
        $exists = (int) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($table)
        )->fetchColumn() > 0;
        smoke("Table {$table}", $exists);
    }

    $cols = ['sort_order', 'deleted_at', 'publication_status'];
    foreach ($cols as $col) {
        $exists = (int) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'properties'
               AND COLUMN_NAME = " . $pdo->quote($col)
        )->fetchColumn() > 0;
        smoke("properties.{$col}", $exists);
    }

    $published = (int) $pdo->query(
        "SELECT COUNT(*) FROM properties
         WHERE deleted_at IS NULL AND is_active = 1 AND publication_status = 'Published'"
    )->fetchColumn();
    smoke('Published properties', $published > 0, (string) $published);

    $admins = (int) $pdo->query('SELECT COUNT(*) FROM admins WHERE is_active = 1')->fetchColumn();
    smoke('Active admin user', $admins > 0, (string) $admins);
} catch (Throwable $e) {
    smoke('Database connection', false, $e->getMessage());
}

$mailUser = trim((string) ($_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?: ''));
$mailPass = trim((string) ($_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?: ''));
smoke(
    'SMTP credentials in .env',
    $mailUser !== '' && $mailPass !== '',
    'optional until email alerts are needed',
    true
);

$phone = siteSetting('contact_phone', '');
$wa = siteSetting('whatsapp_number', '');
$email = siteSetting('contact_email', '');
smoke('Settings contact phone', $phone !== '' && !str_contains($phone, '00000'), $phone !== '' ? $phone : 'missing');
smoke('Settings WhatsApp', $wa !== '' && !str_starts_with($wa, '910000'), $wa !== '' ? $wa : 'missing');
smoke('Settings contact email', $email !== '', $email !== '' ? $email : 'missing');

echo str_repeat('-', 40) . PHP_EOL;
if ($failures > 0) {
    echo "RESULT: {$failures} check(s) failed";
    if ($warnings > 0) {
        echo ", {$warnings} warning(s)";
    }
    echo PHP_EOL;
    exit(1);
}

if ($warnings > 0) {
    echo "RESULT: PASSED WITH {$warnings} WARNING(S)\n";
    exit(0);
}

echo "RESULT: ALL CHECKS PASSED\n";
exit(0);
