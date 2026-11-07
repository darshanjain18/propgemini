<?php
declare(strict_types=1);

require_once __DIR__ . '/settings.php';

function socialIcon(string $name): string
{
    // Line icons use currentColor so footer styling can match the champagne theme.
    $icons = [
        'instagram' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4.1" stroke="currentColor" stroke-width="1.6"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 8.5V11h2.4l-.4 2.6H14V21h-3v-7.4H9V11h2V8.8C11 6.6 12.3 5 15 5c.8 0 1.6.1 2.2.2V7.4H15.8c-1.1 0-1.8.6-1.8 1.7z" fill="currentColor"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.2 9.2H4.4V19.5h2.8V9.2zM5.8 4.5a1.65 1.65 0 1 0 0 3.3 1.65 1.65 0 0 0 0-3.3zM19.6 12.3c0-2.5-1.3-4.1-3.5-4.1-1.4 0-2.2.7-2.6 1.4h-.1V9.2h-2.7c0 .7 0 10.3 0 10.3h2.8v-5.8c0-.3 0-.6.1-.8.3-.6.9-1.2 1.9-1.2 1.3 0 1.9.9 1.9 2.4v5.4h2.8v-6.2z" fill="currentColor"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5.8 10.7 12.8 5.2 18.2h2.1l4.4-4.4 3.7 4.4H19L12.9 10.7 18 5.8h-2.1l-4 4.1L8.6 5.8H5z" fill="currentColor"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="2.5" y="6" width="19" height="12" rx="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M10.4 9.4v5.2L15.2 12l-4.8-2.6z" fill="currentColor"/></svg>',
    ];

    return $icons[$name] ?? '';
}

function socialLinks(): array
{
    $read = static function (string $key): string {
        try {
            return siteSetting($key, '');
        } catch (Throwable $e) {
            return '';
        }
    };

    $xUrl = $read('social_x');
    if ($xUrl === '') {
        $xUrl = $read('social_twitter');
    }
    if ($xUrl === '') {
        // One-time fallback while settings migrate from YouTube → X.
        $legacy = $read('social_youtube');
        if ($legacy !== '' && preg_match('#(x\.com|twitter\.com)#i', $legacy) === 1) {
            $xUrl = $legacy;
        }
    }

    $items = [
        ['id' => 'instagram', 'label' => 'Instagram', 'url' => $read('social_instagram')],
        ['id' => 'facebook', 'label' => 'Facebook', 'url' => $read('social_facebook')],
        ['id' => 'linkedin', 'label' => 'LinkedIn', 'url' => $read('social_linkedin')],
        ['id' => 'x', 'label' => 'X', 'url' => $xUrl],
    ];

    return array_values(array_filter(
        $items,
        static fn (array $item): bool => trim($item['url']) !== ''
    ));
}

function careerRoles(): array
{
    try {
        $raw = trim(siteSetting('careers_roles', ''));
    } catch (Throwable $e) {
        $raw = '';
    }
    if ($raw === '') {
        return [
            ['title' => 'Property Advisor', 'meta' => 'Pune · Mumbai · Gurgaon · Full-time'],
            ['title' => 'Client Relations', 'meta' => 'Mumbai · Gurgaon · Full-time'],
            ['title' => 'Site Visit Coordinator', 'meta' => 'Pune / Mumbai / Gurgaon · Full-time'],
        ];
    }

    $roles = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        $title = $parts[0] ?? '';
        if ($title === '') {
            continue;
        }
        $meta = trim(implode(' · ', array_filter(array_slice($parts, 1))));
        $roles[] = ['title' => $title, 'meta' => $meta];
    }

    return $roles;
}
