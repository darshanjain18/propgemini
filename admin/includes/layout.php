<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function adminNavIcon(string $name): string
{
    $attrs = 'class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
    $icons = [
        'dashboard' => "<svg {$attrs}><rect x=\"3\" y=\"3\" width=\"7\" height=\"9\" rx=\"1.5\"/><rect x=\"14\" y=\"3\" width=\"7\" height=\"5\" rx=\"1.5\"/><rect x=\"14\" y=\"12\" width=\"7\" height=\"9\" rx=\"1.5\"/><rect x=\"3\" y=\"16\" width=\"7\" height=\"5\" rx=\"1.5\"/></svg>",
        'properties' => "<svg {$attrs}><path d=\"M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z\"/></svg>",
        'enquiries' => "<svg {$attrs}><path d=\"M4 5h16v12H8l-4 3V5Z\"/><path d=\"M8 9h8M8 13h5\"/></svg>",
        'visits' => "<svg {$attrs}><rect x=\"3\" y=\"5\" width=\"18\" height=\"16\" rx=\"2\"/><path d=\"M3 10h18M8 3v4M16 3v4\"/></svg>",
        'settings' => "<svg {$attrs}><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4\"/></svg>",
        'legal' => "<svg {$attrs}><path d=\"M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z\"/><path d=\"M14 3v5h5M9 13h6M9 17h6\"/></svg>",
        'activity' => "<svg {$attrs}><path d=\"M4 19V5\"/><path d=\"M4 19h16\"/><path d=\"M8 15l3-4 3 2 4-6\"/></svg>",
        'traffic' => "<svg {$attrs}><path d=\"M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z\"/><path d=\"M12 7v5l3 2\"/></svg>",
        'whatsapp' => "<svg {$attrs}><path d=\"M7 17.5 5.5 21l3.6-1.2A8 8 0 1 0 7 17.5Z\"/><path d=\"M9.2 10.4c.2-.5.4-.5.7-.5h.5c.2 0 .4.1.5.4l.7 1.7c.1.2 0 .4-.1.6l-.4.5c-.1.2-.1.3 0 .5.4.7 1.1 1.4 1.9 1.8.2.1.3.1.5 0l.6-.4c.2-.1.4-.1.5 0l1.5.9c.2.1.3.3.3.5 0 .3 0 .7-.1 1-.1.3-.4.5-.7.6-.3.1-.7.2-1.1.1-1.1-.2-2.2-.8-3.2-1.6-1-.9-1.8-2-2.2-3.2-.2-.5-.2-1 0-1.5Z\"/></svg>",
        'quality' => "<svg {$attrs}><path d=\"M9 11l3 3L22 4\"/><path d=\"M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11\"/></svg>",
        'alerts' => "<svg {$attrs}><path d=\"M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7\"/><path d=\"M13.7 21a2 2 0 0 1-3.4 0\"/></svg>",
        'security' => "<svg {$attrs}><path d=\"M12 3l8 4v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7l8-4Z\"/></svg>",
        'trash' => "<svg {$attrs}><path d=\"M3 6h18\"/><path d=\"M8 6V4h8v2\"/><path d=\"M19 6l-1 14H6L5 6\"/></svg>",
        'site' => "<svg {$attrs}><circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M3 12h18M12 3c2.5 2.8 2.5 15.2 0 18M12 3c-2.5 2.8-2.5 15.2 0 18\"/></svg>",
        'guide' => "<svg {$attrs}><path d=\"M4 19.5A2.5 2.5 0 0 1 6.5 17H20\"/><path d=\"M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z\"/><path d=\"M8 7h8M8 11h6\"/></svg>",
        'news' => "<svg {$attrs}><path d=\"M4 5h16v14H4z\"/><path d=\"M8 9h8M8 13h5\"/></svg>",
        'gallery' => "<svg {$attrs}><rect x=\"3\" y=\"5\" width=\"18\" height=\"14\" rx=\"2\"/><path d=\"m3 15 5-4 4 3 3-2 6 5\"/></svg>",
        'logout' => "<svg {$attrs}><path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><path d=\"M16 17l5-5-5-5M21 12H9\"/></svg>",
    ];

    return $icons[$name] ?? '';
}

function adminLayoutStart(string $title, string $active = 'dashboard'): void
{
    $user = requireAdmin();
    $flash = adminTakeFlash();
    $name = (string) ($user['name'] ?? 'Admin');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> · PropGemini Admin</title>
    <link rel="stylesheet" href="<?= h(adminUrl('assets/admin.css')) ?>">
</head>
<body class="admin-body">
<aside class="admin-sidebar">
    <a class="brand" href="<?= h(adminUrl('dashboard.php')) ?>">
        <strong>PropGemini Admin</strong>
    </a>
    <nav>
        <a class="<?= $active === 'dashboard' ? 'is-active' : '' ?>" href="<?= h(adminUrl('dashboard.php')) ?>"><?= adminNavIcon('dashboard') ?><span>Dashboard</span></a>
        <a class="<?= $active === 'guide' ? 'is-active' : '' ?>" href="<?= h(adminUrl('guide.php')) ?>"><?= adminNavIcon('guide') ?><span>Getting started</span></a>
        <a class="<?= $active === 'properties' ? 'is-active' : '' ?>" href="<?= h(adminUrl('properties/index.php')) ?>"><?= adminNavIcon('properties') ?><span>Properties</span></a>
        <a class="<?= $active === 'news' ? 'is-active' : '' ?>" href="<?= h(adminUrl('news/index.php')) ?>"><?= adminNavIcon('news') ?><span>News</span></a>
        <a class="<?= $active === 'gallery' ? 'is-active' : '' ?>" href="<?= h(adminUrl('gallery/index.php')) ?>"><?= adminNavIcon('gallery') ?><span>Gallery</span></a>
        <a class="<?= $active === 'enquiries' ? 'is-active' : '' ?>" href="<?= h(adminUrl('enquiries/index.php')) ?>"><?= adminNavIcon('enquiries') ?><span>Enquiries</span></a>
        <a class="<?= $active === 'visits' ? 'is-active' : '' ?>" href="<?= h(adminUrl('visits/index.php')) ?>"><?= adminNavIcon('visits') ?><span>Site visits</span></a>
        <a class="<?= $active === 'alerts' ? 'is-active' : '' ?>" href="<?= h(adminUrl('alerts.php')) ?>"><?= adminNavIcon('alerts') ?><span>Alerts</span></a>
        <a class="<?= $active === 'quality' ? 'is-active' : '' ?>" href="<?= h(adminUrl('quality.php')) ?>"><?= adminNavIcon('quality') ?><span>Listing quality</span></a>
        <a class="<?= $active === 'activity' ? 'is-active' : '' ?>" href="<?= h(adminUrl('activity.php')) ?>"><?= adminNavIcon('activity') ?><span>Recent activity</span></a>
        <a class="<?= $active === 'traffic' ? 'is-active' : '' ?>" href="<?= h(adminUrl('traffic.php')) ?>"><?= adminNavIcon('traffic') ?><span>Website traffic</span></a>
        <a class="<?= $active === 'whatsapp' ? 'is-active' : '' ?>" href="<?= h(adminUrl('whatsapp.php')) ?>"><?= adminNavIcon('whatsapp') ?><span>WhatsApp tracking</span></a>
        <a class="<?= $active === 'legal' ? 'is-active' : '' ?>" href="<?= h(adminUrl('legal.php')) ?>"><?= adminNavIcon('legal') ?><span>Legal pages</span></a>
        <a class="<?= $active === 'security' ? 'is-active' : '' ?>" href="<?= h(adminUrl('security.php')) ?>"><?= adminNavIcon('security') ?><span>Security &amp; audit</span></a>
        <a class="<?= $active === 'settings' ? 'is-active' : '' ?>" href="<?= h(adminUrl('settings.php')) ?>"><?= adminNavIcon('settings') ?><span>Settings</span></a>
    </nav>
    <div class="sidebar-foot">
        <a href="<?= h(webPath()) ?>" target="_blank" rel="noopener"><?= adminNavIcon('site') ?><span>View site</span></a>
        <a href="<?= h(adminUrl('logout.php')) ?>"><?= adminNavIcon('logout') ?><span>Log out</span></a>
    </div>
</aside>
<div class="admin-main">
    <header class="admin-top">
        <div>
            <h1><?= h($title) ?></h1>
        </div>
        <div class="admin-user"><?= h($name) ?></div>
    </header>
    <?php if ($flash): ?>
        <div class="flash flash-<?= h((string) $flash['type']) ?>"><?= h((string) $flash['message']) ?></div>
    <?php endif; ?>
    <div class="admin-content">
    <?php
}

function adminLayoutEnd(): void
{
    ?>
    </div>
</div>
<script src="<?= h(adminUrl('assets/admin.js')) ?>"></script>
</body>
</html>
    <?php
}
