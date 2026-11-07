<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

function siteSettings(array $keys = []): array
{
    static $cache = null;
    if ($cache === null) {
        $pdo = getDatabaseConnection();
        $cache = [];
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
        }
    }

    if ($keys === []) {
        return $cache;
    }

    $out = [];
    foreach ($keys as $key) {
        $out[$key] = $cache[$key] ?? '';
    }
    return $out;
}

function siteSetting(string $key, string $default = ''): string
{
    $all = siteSettings();
    $value = $all[$key] ?? $default;
    return $value !== '' ? $value : $default;
}

function upsertSiteSetting(string $key, string $value): void
{
    $pdo = getDatabaseConnection();
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute([$key, $value]);
}

function legalPageDefaults(string $page): array
{
    if ($page === 'privacy') {
        return [
            'title' => 'Privacy Policy',
            'body' => "Last updated: November 2026\n\nThis Privacy Policy explains how PropGemini handles personal information shared through property enquiries and site-visit requests.",
        ];
    }

    return [
        'title' => 'Terms & Conditions',
        'body' => "Last updated: November 2026\n\nThese Terms & Conditions govern use of the PropGemini website and property enquiries submitted through it.",
    ];
}

function legalPageContent(string $page): array
{
    $page = $page === 'privacy' ? 'privacy' : 'terms';
    $defaults = legalPageDefaults($page);
    $prefix = $page === 'privacy' ? 'legal_privacy' : 'legal_terms';

    return [
        'title' => siteSetting($prefix . '_title', $defaults['title']),
        'body' => siteSetting($prefix . '_body', $defaults['body']),
        'updated_at' => siteSetting($prefix . '_updated_at', ''),
    ];
}

function renderLegalBody(string $body): string
{
    $body = trim(str_replace(["\r\n", "\r"], "\n", $body));
    if ($body === '') {
        return '<p>Content coming soon.</p>';
    }

    $parts = preg_split("/\n{2,}/", $body) ?: [];
    $html = '';

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        $lines = explode("\n", $part);
        $first = trim($lines[0]);

        // Numbered section heading: "1. Title"
        if (preg_match('/^\d+\.\s+.+$/', $first) === 1 && count($lines) > 1) {
            $html .= '<h2>' . h($first) . '</h2>';
            $rest = trim(implode("\n", array_slice($lines, 1)));
            if ($rest !== '') {
                $html .= '<p>' . nl2br(h($rest), false) . '</p>';
            }
            continue;
        }

        if (preg_match('/^\d+\.\s+.+$/', $first) === 1) {
            $html .= '<h2>' . h($first) . '</h2>';
            continue;
        }

        if (preg_match('/^last updated:/i', $first) === 1) {
            $html .= '<p class="legal-updated">' . h($first) . '</p>';
            continue;
        }

        $html .= '<p>' . nl2br(h($part), false) . '</p>';
    }

    return $html !== '' ? $html : '<p>Content coming soon.</p>';
}
