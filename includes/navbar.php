<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$items = navItems();
$homeUrl = $items[0]['href'];
$enquireUrl = $items[array_key_last($items)]['href'];
?>
<div class="announcement">आगे बढ़ते रहो <span class="announcement-sep">|</span> Keep Progressing</div>
<div class="site-bar">
<header class="nav">
    <a class="brand" href="<?= h($homeUrl) ?>" aria-label="PropGemini home">
        <img class="brand-mark" src="<?= h(webPath('public/images/logo-mark.png')) ?>" alt="" width="36" height="36" decoding="async">
        <span class="brand-name"><span class="brand-prop">Prop</span><span class="brand-gemini">Gemini</span></span>
    </a>

    <nav class="navlinks" aria-label="Main navigation">
        <?php foreach ($items as $item): ?>
            <a href="<?= h($item['href']) ?>"<?= !empty($item['cta']) ? ' class="enquire-link"' : '' ?>>
                <?= h($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <a class="btn nav-cta" href="<?= h($enquireUrl) ?>">Enquire Now <span>↗</span></a>
    <button class="mobile-toggle" aria-label="Open navigation" aria-expanded="false" id="mobileToggle">☰</button>

    <nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
        <?php foreach ($items as $item): ?>
            <a href="<?= h($item['href']) ?>"><?= h($item['label']) ?></a>
        <?php endforeach; ?>
    </nav>
</header>
</div>
