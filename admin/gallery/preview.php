<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

$pdo = getDatabaseConnection();
$items = $pdo->query('SELECT * FROM gallery_items ORDER BY sort_order ASC, id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery preview</title>
    <link rel="stylesheet" href="<?= h(webPath('public/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= h(webPath('public/css/design-system.css')) ?>">
    <style>
        body { background: #f7f3eb; color: #141311; }
        .preview-bar { position: sticky; top: 0; z-index: 5; display: flex; justify-content: space-between; gap: 16px; align-items: center; padding: 16px 24px; background: #141311; color: #f7f3eb; }
        .preview-bar a { color: #f7f3eb; border-bottom: 1px solid #c4a46d; }
        .preview-note { max-width: 760px; margin: 28px auto 0; padding: 0 24px; font-size: 16px; line-height: 1.6; }
        .preview-note + .pg-section { padding-top: 24px; }
    </style>
</head>
<body>
    <div class="preview-bar">
        <strong>Gallery preview</strong>
        <a href="<?= h(adminUrl('gallery/index.php')) ?>">Back to gallery</a>
    </div>
    <p class="preview-note">This is the gallery grid visitors see. The first row is a built sample with a photograph and a film. Your own uploads follow it.</p>
    <section class="pg-section">
        <div class="wrap gallery-grid">
            <?php foreach (galleryLayoutSamples() as $item): ?>
                <?php renderGalleryCard($item); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="pg-section">
        <div class="wrap">
            <h2>Your gallery</h2>
            <?php if ($items === []): ?>
                <p>No uploads yet. Add an image or video, then open this preview again.</p>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($items as $item): ?>
                        <?php renderGalleryCard($item); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
