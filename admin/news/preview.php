<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();

$selected = (string) ($_GET['layout'] ?? 'spotlight');
if (!isset(newsLayouts()[$selected])) {
    $selected = 'spotlight';
}
$returnId = (int) ($_GET['id'] ?? 0);
$returnPath = 'news/edit.php?layout=' . rawurlencode($selected);
if ($returnId > 0) {
    $returnPath .= '&id=' . $returnId;
}
$samples = newsLayoutSamples();
$live = $returnId > 0 ? newsPostById($returnId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News layout preview</title>
    <link rel="stylesheet" href="<?= h(webPath('public/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= h(webPath('public/css/design-system.css')) ?>">
    <style>
        body { background: #f7f3eb; color: #141311; }
        .preview-bar { position: sticky; top: 0; z-index: 5; display: flex; justify-content: space-between; gap: 16px; align-items: center; padding: 16px 24px; background: #f4ecdc; color: #1c1915; border-bottom: 1px solid #e4d5b8; }
        .preview-bar a { color: #1c1915; }
        .preview-nav { display: flex; flex-wrap: wrap; gap: 10px; }
        .preview-nav a { color: #3c3428; border: 1px solid #d9c7a4; border-radius: 999px; padding: 8px 14px; font-size: 13px; background: #fffdf8; }
        .preview-nav a.is-current { background: #c4a46d; color: #1c1915; border-color: #c4a46d; }
        .preview-back { border-bottom: 1px solid #c4a46d; }
        .preview-note { max-width: 760px; margin: 28px auto 0; padding: 0 24px; font-size: 15px; line-height: 1.6; }
        .layout-stage { overflow: hidden; }
        .layout-sample { display: none; margin: 28px auto 48px; padding: 8px; border-radius: 28px; }
        .layout-sample.is-current { display: block; animation: layoutShift .35s ease; }
        .layout-sample h2.sample-label { max-width: 760px; margin: 0 auto 8px; padding: 0 24px; font-family: Manrope, sans-serif; font-size: 14px; letter-spacing: .08em; text-transform: uppercase; color: #8d6b32; }
        @keyframes layoutShift { from { opacity: 0; transform: translateX(36px); } to { opacity: 1; transform: none; } }
    </style>
</head>
<body>
    <div class="preview-bar">
        <strong><?= $live ? 'Live article layout preview' : 'Sample of every news layout' ?></strong>
        <nav class="preview-nav">
            <?php foreach (newsLayouts() as $value => $label): ?>
                <a class="<?= $selected === $value ? 'is-current' : '' ?>" data-layout="<?= h($value) ?>" href="#layout-<?= h($value) ?>"><?= h(newsLayoutLabel($value)) ?></a>
            <?php endforeach; ?>
        </nav>
        <a class="preview-back" id="previewBack" href="<?= h(adminUrl($returnPath)) ?>">Back to the form</a>
    </div>
    <p class="preview-note"><?= $live
        ? 'Switching layouts below only changes the preview — your saved content stays intact until you choose a layout and save the form.'
        : 'Click a layout and the sample shifts to that design. The gold pill is the one you are viewing. Go back and save your news in that layout.' ?></p>
    <div class="layout-stage">
    <?php if ($live !== null): ?>
        <?php foreach (array_keys(newsLayouts()) as $layout): ?>
            <?php $item = array_merge($live, ['layout' => $layout]); ?>
            <section class="layout-sample<?= $selected === $layout ? ' is-current' : '' ?>" id="layout-<?= h($layout) ?>" data-layout="<?= h($layout) ?>">
                <h2 class="sample-label"><?= h(newsLayoutLabel($layout)) ?> · <?= h((string) $live['title']) ?></h2>
                <div class="wrap">
                    <?php renderNewsItem($item); ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach ($samples as $layout => $item): ?>
            <section class="layout-sample<?= $selected === $layout ? ' is-current' : '' ?>" id="layout-<?= h($layout) ?>" data-layout="<?= h($layout) ?>">
                <h2 class="sample-label"><?= h(newsLayoutLabel($layout)) ?></h2>
                <div class="wrap">
                    <?php renderNewsItem($item); ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <script>
        const links = document.querySelectorAll('.preview-nav a');
        const samples = document.querySelectorAll('.layout-sample');
        const back = document.getElementById('previewBack');
        const backBase = back ? back.href.split('?')[0] : '';
        const returnId = <?= (int) $returnId ?>;

        const showLayout = (layout) => {
            links.forEach((link) => link.classList.toggle('is-current', link.dataset.layout === layout));
            samples.forEach((sample) => {
                sample.classList.toggle('is-current', sample.dataset.layout === layout);
            });
            const query = 'layout=' + encodeURIComponent(layout) + (returnId > 0 ? '&id=' + returnId : '');
            history.replaceState(null, '', '?' + query);
            if (back) back.href = backBase + '?layout=' + encodeURIComponent(layout) + (returnId > 0 ? '&id=' + returnId : '');
        };

        links.forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                showLayout(link.dataset.layout);
            });
        });
    </script>
</body>
</html>
