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

/**
 * Parse a legal document body into structured sections for the public layout.
 *
 * @return array{
 *   updated_label: string,
 *   updated_short: string,
 *   preamble: list<string>,
 *   sections: list<array{id: string, number: string, title: string, heading: string, paragraphs: list<string>}>
 * }
 */
function parseLegalDocument(string $body): array
{
    $body = trim(str_replace(["\r\n", "\r"], "\n", $body));
    $result = [
        'updated_label' => '',
        'updated_short' => '',
        'preamble' => [],
        'sections' => [],
    ];

    if ($body === '') {
        return $result;
    }

    $parts = preg_split("/\n{2,}/", $body) ?: [];
    $current = null;

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        $lines = explode("\n", $part);
        $first = trim($lines[0]);

        if (preg_match('/^last updated:\s*(.+)$/i', $first, $m) === 1) {
            $result['updated_label'] = $first;
            $result['updated_short'] = trim($m[1]);
            continue;
        }

        if (preg_match('/^(\d+)\.\s+(.+)$/', $first, $m) === 1) {
            if ($current !== null) {
                $result['sections'][] = $current;
            }
            $number = $m[1];
            $title = trim($m[2]);
            $current = [
                'id' => 'section-' . $number,
                'number' => $number,
                'title' => $title,
                'heading' => $number . '. ' . $title,
                'paragraphs' => [],
            ];
            $rest = trim(implode("\n", array_slice($lines, 1)));
            if ($rest !== '') {
                $current['paragraphs'][] = $rest;
            }
            continue;
        }

        if ($current !== null) {
            $current['paragraphs'][] = $part;
        } else {
            $result['preamble'][] = $part;
        }
    }

    if ($current !== null) {
        $result['sections'][] = $current;
    }

    return $result;
}

function renderLegalBody(string $body): string
{
    $doc = parseLegalDocument($body);
    if ($doc['sections'] === [] && $doc['preamble'] === [] && $doc['updated_label'] === '') {
        return '<p>Content coming soon.</p>';
    }

    $html = '';
    if ($doc['updated_label'] !== '') {
        $html .= '<p class="legal-updated">' . h($doc['updated_label']) . '</p>';
    }

    foreach ($doc['preamble'] as $paragraph) {
        $html .= '<p>' . nl2br(h($paragraph), false) . '</p>';
    }

    foreach ($doc['sections'] as $section) {
        $html .= '<h2 id="' . h($section['id']) . '">' . h($section['heading']) . '</h2>';
        foreach ($section['paragraphs'] as $paragraph) {
            $html .= '<p>' . nl2br(h($paragraph), false) . '</p>';
        }
    }

    return $html !== '' ? $html : '<p>Content coming soon.</p>';
}
