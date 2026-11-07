<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/content.php';

$pageTitle = 'News | PropGemini';
$pageDescription = 'New homes, price updates and offers from PropGemini.';
$bodyClass = 'news-page';
$posts = publishedNews();

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <?php renderPublicPageHero(
        'News',
        'What is new',
        'New homes, price changes and offers, published by the PropGemini desk.',
        'at PropGemini.'
    ); ?>
    <section class="pg-section">
        <div class="wrap news-list">
            <?php if ($posts === []): ?>
                <p class="pg-empty">News will appear here once the desk publishes an update.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <?php renderNewsItem($post); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
