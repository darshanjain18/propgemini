<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/content.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = newsPostBySlug($slug);

if ($post === null) {
    http_response_code(404);
    $pageTitle = 'Article not found | PropGemini';
    $pageDescription = 'This news article is unavailable.';
    $bodyClass = 'news-page';
    $pageStyles = ['public/css/news.css'];
    require dirname(__DIR__) . '/includes/header.php';
    require dirname(__DIR__) . '/includes/navbar.php';
    echo '<main class="pg-news-article"><div class="wrap"><div class="pg-news-empty"><h1 style="margin:0 0 8px;font-family:var(--serif,Georgia,serif);color:#263b3d;">Article not found</h1><p style="margin:0;">This story is not published or the link is incorrect. <a href="' . h(webPath('pages/news')) . '">Back to News</a></p></div></div></main>';
    require dirname(__DIR__) . '/includes/footer.php';
    exit;
}

$seoTitle = trim((string) ($post['seo_title'] ?? ''));
$seoDesc = trim((string) ($post['seo_description'] ?? ''));
$pageTitle = ($seoTitle !== '' ? $seoTitle : (string) $post['title']) . ' | PropGemini';
$pageDescription = $seoDesc !== '' ? $seoDesc : (string) ($post['summary'] ?? $pageTitle);
$bodyClass = 'news-page';
$pageStyles = ['public/css/news.css'];
$pageScripts = ['public/js/news.js'];

$cover = trim((string) ($post['image_path'] ?? ''));
$social = trim((string) ($post['social_image_path'] ?? ''));
if ($social !== '') {
    $ogImage = $social;
} elseif ($cover !== '') {
    $ogImage = $cover;
} else {
    $ogImage = null;
}

$cat = trim((string) ($post['category_name'] ?? $post['kicker'] ?? ''));
$date = formatNewsDate((string) ($post['published_at'] ?? $post['created_at'] ?? ''));
$read = (int) ($post['reading_time'] ?? 1);
$layout = (string) ($post['layout'] ?? 'editorial');
$ctaText = trim((string) ($post['cta_text'] ?? ''));
$ctaUrl = trim((string) ($post['cta_url'] ?? ''));
$offerTerms = trim((string) ($post['offer_terms'] ?? ''));
$highlight = trim((string) ($post['highlight'] ?? ''));
$gallery = $post['gallery'] ?? [];

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="pg-news-article">
    <article class="wrap pg-news-article-wrap" data-news-reveal>
        <p><a href="<?= h(webPath('pages/news')) ?>" style="color:#c6a36a;text-decoration:none;font-size:13px;font-weight:600;">← Back to News</a></p>
        <?php if ($cat !== ''): ?>
            <p class="pg-news-cat"><?= h($cat) ?></p>
        <?php endif; ?>
        <h1><?= h((string) $post['title']) ?></h1>
        <div class="pg-news-meta" style="margin-bottom:8px;">
            <?php if ($date !== ''): ?><span><?= h($date) ?></span><?php endif; ?>
            <span><?= $read ?> min read</span>
            <?php if (trim((string) ($post['author'] ?? '')) !== ''): ?>
                <span><?= h((string) $post['author']) ?></span>
            <?php endif; ?>
            <span><?= h(newsLayoutLabel($layout)) ?></span>
        </div>
        <?php if (trim((string) ($post['summary'] ?? '')) !== ''): ?>
            <p style="margin:0 0 8px;color:rgba(38,59,61,.72);font-size:17px;line-height:1.6;"><?= h((string) $post['summary']) ?></p>
        <?php endif; ?>

        <?php if ($layout === 'offer' && $highlight !== ''): ?>
            <p class="pg-news-badge pg-news-badge-gold" style="display:inline-flex;margin:12px 0;"><?= h($highlight) ?></p>
        <?php endif; ?>

        <?php if ($cover !== '' && $layout !== 'note'): ?>
            <figure class="pg-news-article-cover">
                <img src="<?= h(webPath($cover)) ?>" alt="<?= h((string) ($post['image_alt'] ?? $post['title'])) ?>" width="1200" height="675" decoding="async">
                <?php if (trim((string) ($post['image_caption'] ?? '')) !== ''): ?>
                    <figcaption style="padding:10px 4px 0;color:rgba(38,59,61,.55);font-size:13px;"><?= h((string) $post['image_caption']) ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <?php if ($highlight !== '' && $layout !== 'offer'): ?>
            <p style="margin:0 0 16px;color:#8a6a35;font-weight:600;"><?= h($highlight) ?></p>
        <?php endif; ?>

        <div class="pg-news-article-body">
            <?php renderNewsBody((string) ($post['body'] ?? '')); ?>
        </div>

        <?php if (is_array($gallery) && $gallery !== []): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin:24px 0;">
                <?php foreach ($gallery as $gImg): ?>
                    <img src="<?= h(webPath((string) $gImg)) ?>" alt="" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px;" loading="lazy" decoding="async">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (trim((string) ($post['video_url'] ?? '')) !== ''): ?>
            <?php $video = (string) $post['video_url']; ?>
            <p style="margin:20px 0;"><a href="<?= h($video) ?>" target="_blank" rel="noopener" style="color:#c6a36a;">Watch related video</a></p>
        <?php endif; ?>

        <?php if ($layout === 'offer' && $offerTerms !== ''): ?>
            <p style="margin:20px 0 0;padding:14px 16px;border-radius:12px;background:rgba(220,200,165,.28);color:rgba(38,59,61,.75);font-size:13px;line-height:1.55;"><strong>Offer terms:</strong> <?= h($offerTerms) ?></p>
        <?php endif; ?>

        <?php if ($ctaText !== '' && $ctaUrl !== ''): ?>
            <?php
            $ctaHref = preg_match('#^https?://#i', $ctaUrl) === 1 ? $ctaUrl : webPath(ltrim($ctaUrl, '/'));
            ?>
            <p style="margin:28px 0 0;"><a class="pg-news-btn" href="<?= h($ctaHref) ?>"><?= h($ctaText) ?> <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a></p>
        <?php endif; ?>
    </article>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
