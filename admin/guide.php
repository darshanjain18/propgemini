<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/notifications.php';

$pdo = getDatabaseConnection();
$settings = siteSettings([
    'whatsapp_number',
    'contact_phone',
    'contact_email',
    'contact_address',
]);

$uploadsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
$checks = [
    [
        'label' => 'Contact phone configured',
        'ok' => !isPlaceholderContactValue('contact_phone', (string) ($settings['contact_phone'] ?? '')),
        'href' => adminUrl('settings.php'),
    ],
    [
        'label' => 'WhatsApp number configured',
        'ok' => !isPlaceholderContactValue('whatsapp_number', (string) ($settings['whatsapp_number'] ?? '')),
        'href' => adminUrl('settings.php'),
    ],
    [
        'label' => 'Contact email configured',
        'ok' => !isPlaceholderContactValue('contact_email', (string) ($settings['contact_email'] ?? '')),
        'href' => adminUrl('settings.php'),
    ],
    [
        'label' => 'Uploads folder writable',
        'ok' => is_dir($uploadsDir) && is_writable($uploadsDir),
        'href' => null,
    ],
    [
        'label' => 'At least one published property',
        'ok' => (int) $pdo->query(
            "SELECT COUNT(*) FROM properties
             WHERE deleted_at IS NULL AND is_active = 1 AND publication_status = 'Published'"
        )->fetchColumn() > 0,
        'href' => adminUrl('properties/index.php'),
    ],
];

adminLayoutStart('Getting started', 'guide');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Client walkthrough</p>
        <h2>How to manage PropGemini</h2>
        <p>Follow this checklist the first time you use the admin panel, then use it whenever you publish a new listing.</p>
    </div>
    <div class="dash-actions">
        <a class="btn" href="<?= h(adminUrl('properties/edit')) ?>">Add property</a>
        <a class="btn secondary" href="<?= h(webPath()) ?>" target="_blank" rel="noopener">View site</a>
    </div>
</section>

<div class="panel" style="margin-bottom:16px;">
    <h3 style="margin-top:0;">Setup health</h3>
    <ul class="publish-checklist">
        <?php foreach ($checks as $check): ?>
            <li class="<?= $check['ok'] ? 'is-ok' : 'is-missing' ?>">
                <strong><?= $check['ok'] ? '✓' : '○' ?> <?= h($check['label']) ?></strong>
                <?php if (!$check['ok'] && !empty($check['href'])): ?>
                    <a class="text-link" href="<?= h((string) $check['href']) ?>">Fix</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="dash-grid">
    <div class="panel">
        <h3 style="margin-top:0;">1. Set contact details</h3>
        <ol class="guide-steps">
            <li>Open <a href="<?= h(adminUrl('settings.php')) ?>">Settings</a>.</li>
            <li>Save WhatsApp number, phone, email and address.</li>
            <li>These values appear in the footer, Contact page and lead alerts.</li>
            <li>Add Instagram, Facebook, LinkedIn and YouTube URLs if you want brand icons under Contact.</li>
            <li>Careers lives in the footer only — edit open roles in the same settings form.</li>
        </ol>
    </div>
    <div class="panel">
        <h3 style="margin-top:0;">2. Add a property</h3>
        <ol class="guide-steps">
            <li>Go to <a href="<?= h(adminUrl('properties/edit')) ?>">Add property</a>.</li>
            <li>Enter name, city, area, type, BHK and overview.</li>
            <li>Set a starting price.</li>
        </ol>
    </div>
    <div class="panel">
        <h3 style="margin-top:0;">3. Upload media</h3>
        <ol class="guide-steps">
            <li>Add at least one gallery image (required to publish).</li>
            <li>Optional: video, floor plan, brochure PDF.</li>
            <li>Drag images to reorder; the first image is primary.</li>
        </ol>
    </div>
    <div class="panel">
        <h3 style="margin-top:0;">4. Publish &amp; check live</h3>
        <ol class="guide-steps">
            <li>Complete the Publish checklist on the edit page.</li>
            <li>Set Publication to <strong>Published</strong> and save.</li>
            <li>Open <a href="<?= h(webPath('pages/properties')) ?>" target="_blank" rel="noopener">Projects</a> and confirm the listing.</li>
        </ol>
    </div>
</div>

<div class="panel" style="margin-top:16px;">
    <h3 style="margin-top:0;">Day-to-day ops</h3>
    <ul class="guide-steps" style="list-style:disc;padding-left:18px;">
        <li><a href="<?= h(adminUrl('enquiries/index.php')) ?>">Enquiries</a> — update status and notes for new leads.</li>
        <li><a href="<?= h(adminUrl('visits/index.php')) ?>">Site visits</a> — confirm, complete or reschedule visits.</li>
        <li><a href="<?= h(adminUrl('alerts.php')) ?>">Alerts</a> — run automation checks for reminders and stale leads.</li>
        <li><a href="<?= h(adminUrl('properties/index.php')) ?>">Properties</a> — drag rows to control public listing order.</li>
        <li><a href="<?= h(adminUrl('activity.php')) ?>">Recent activity</a> — audit admin changes.</li>
    </ul>
</div>
<?php adminLayoutEnd(); ?>
