<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/content.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

$pageTitle = 'News & Updates | PropGemini';
$pageDescription = 'Latest property launches, market insights, offers and company updates from PropGemini.';
$bodyClass = 'news-page';
$pageStyles = ['public/css/news.css'];
$pageScripts = ['public/js/news.js'];

$featured = featuredNewsPost();
$highlights = highlightNewsPosts(3);
$categories = activeNewsCategories(true);
$searchQ = trim((string) ($_GET['q'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'newest');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;

$allPosts = publishedNews(null, 200);
if ($searchQ !== '') {
    $needle = mb_strtolower($searchQ);
    $allPosts = array_values(array_filter($allPosts, static function (array $p) use ($needle): bool {
        $hay = mb_strtolower(
            (string) ($p['title'] ?? '') . ' ' .
            (string) ($p['summary'] ?? '') . ' ' .
            (string) ($p['category_name'] ?? '') . ' ' .
            (string) ($p['body'] ?? '')
        );
        return str_contains($hay, $needle);
    }));
}
if ($sort === 'oldest') {
    usort($allPosts, static fn ($a, $b) => strcmp((string) ($a['published_at'] ?? ''), (string) ($b['published_at'] ?? '')));
} elseif ($sort === 'title') {
    usort($allPosts, static fn ($a, $b) => strcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? '')));
}

$totalPosts = count($allPosts);
$totalPages = max(1, (int) ceil($totalPosts / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$posts = array_slice($allPosts, ($page - 1) * $perPage, $perPage);

$heroImage = 'public/images/projects/skyline-dusk.jpg';
if (!is_file(dirname(__DIR__) . '/' . $heroImage)) {
    $heroImage = 'public/images/hero.jpg';
}

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <section class="pg-news-hero" aria-labelledby="pg-news-hero-heading">
        <svg class="pg-news-hero-waves" viewBox="0 0 800 400" fill="none" aria-hidden="true" focusable="false">
            <path class="pg-news-hero-wave" d="M40 280C120 220 180 180 260 200C360 226 400 300 500 280C600 260 640 160 760 140" stroke="currentColor" stroke-width="1.5"/>
            <path class="pg-news-hero-wave" d="M20 320C110 270 170 240 250 255C350 275 410 340 510 310C610 280 680 190 780 170" stroke="currentColor" stroke-width="1" opacity="0.55"/>
        </svg>
        <div class="wrap pg-news-hero-inner">
            <div data-news-reveal>
                <p class="pg-news-kicker">News &amp; Updates</p>
                <h1 id="pg-news-hero-heading">What’s New at <em>PropGemini</em></h1>
                <p class="pg-news-hero-lead">Latest property launches, market insights, offers and company updates.</p>
            </div>
            <figure class="pg-news-hero-visual" data-news-reveal>
                <img src="<?= h(webPath($heroImage)) ?>" alt="City skyline at dusk" width="960" height="600" decoding="async">
            </figure>
        </div>
    </section>

    <section class="pg-news-section" aria-label="Featured news">
        <div class="wrap">
            <?php if ($featured === null && $posts === []): ?>
                <div class="pg-news-empty" data-news-reveal>
                    <h2 style="margin:0 0 8px;font-family:var(--serif,Georgia,serif);color:#263b3d;">Stories coming soon</h2>
                    <p style="margin:0;">The PropGemini desk has not published an update yet. Check back shortly for launches, insights and offers.</p>
                </div>
            <?php else: ?>
                <div class="pg-news-feature-grid">
                    <?php if ($featured !== null): ?>
                        <?php
                        $featImg = trim((string) ($featured['image_path'] ?? ''));
                        if ($featImg === '') {
                            $featImg = 'public/images/horizon.jpg';
                        }
                        $featGallery = $featured['gallery'] ?? [];
                        $featCat = trim((string) ($featured['category_name'] ?? $featured['kicker'] ?? 'Update'));
                        $featDate = formatNewsDate((string) ($featured['published_at'] ?? $featured['created_at'] ?? ''));
                        $featRead = (int) ($featured['reading_time'] ?? 1);
                        ?>
                        <article class="pg-news-featured" data-news-reveal>
                            <div class="pg-news-featured-media">
                                <div class="pg-news-badges">
                                    <span class="pg-news-badge pg-news-badge-gold">Featured</span>
                                    <?php if ($featCat !== ''): ?>
                                        <span class="pg-news-badge"><?= h($featCat) ?></span>
                                    <?php endif; ?>
                                </div>
                                <img src="<?= h(webPath($featImg)) ?>" alt="<?= h((string) ($featured['image_alt'] ?? $featured['title'])) ?>" width="800" height="560" decoding="async">
                            </div>
                            <div class="pg-news-featured-copy">
                                <?php if ($featCat !== ''): ?>
                                    <p class="pg-news-cat"><?= h($featCat) ?></p>
                                <?php endif; ?>
                                <h2><a href="<?= h((string) $featured['url']) ?>"><?= h((string) $featured['title']) ?></a></h2>
                                <?php if (trim((string) ($featured['summary'] ?? '')) !== ''): ?>
                                    <p><?= h((string) $featured['summary']) ?></p>
                                <?php endif; ?>
                                <div class="pg-news-meta">
                                    <?php if ($featDate !== ''): ?><span><?= h($featDate) ?></span><?php endif; ?>
                                    <span><?= $featRead ?> min read</span>
                                </div>
                                <a class="pg-news-btn" href="<?= h((string) $featured['url']) ?>">Read Full Story <i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    <?php else: ?>
                        <div class="pg-news-featured" data-news-reveal style="display:grid;place-items:center;padding:40px;">
                            <p class="pg-news-empty" style="margin:0;border:0;background:transparent;">No featured story selected yet.</p>
                        </div>
                    <?php endif; ?>

                    <aside class="pg-news-highlights" data-news-reveal aria-label="Latest highlights">
                        <div class="pg-news-highlights-head">
                            <h2>Latest Highlights</h2>
                            <a href="#pg-news-grid">View All News</a>
                        </div>
                        <?php if ($highlights === []): ?>
                            <p style="margin:12px 8px;color:rgba(38,59,61,.6);font-size:13px;">Highlights will appear here once stories are published.</p>
                        <?php else: ?>
                            <?php foreach ($highlights as $item): ?>
                                <?php
                                $thumb = trim((string) ($item['image_path'] ?? ''));
                                if ($thumb === '') {
                                    $thumb = 'public/images/canopy.jpg';
                                }
                                $hDate = formatNewsDate((string) ($item['published_at'] ?? $item['created_at'] ?? ''));
                                $hRead = (int) ($item['reading_time'] ?? 1);
                                ?>
                                <a class="pg-news-highlight-item" href="<?= h((string) $item['url']) ?>">
                                    <img src="<?= h(webPath($thumb)) ?>" alt="" width="72" height="56" loading="lazy" decoding="async">
                                    <div>
                                        <h3><?= h((string) $item['title']) ?></h3>
                                        <span><?= h($hDate) ?><?= $hDate !== '' ? ' | ' : '' ?><?= $hRead ?> min read</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="wrap" aria-label="Search news">
        <form method="get" class="pg-news-search" action="<?= h(webPath('pages/news')) ?>">
            <label class="visually-hidden" for="pg-news-search-q">Search headlines</label>
            <input id="pg-news-search-q" type="search" name="q" value="<?= h($searchQ) ?>" placeholder="Search headlines…" maxlength="120">
            <label class="visually-hidden" for="pg-news-search-sort">Sort</label>
            <select id="pg-news-search-sort" name="sort">
                <option value="newest"<?= $sort === 'newest' ? ' selected' : '' ?>>Newest</option>
                <option value="oldest"<?= $sort === 'oldest' ? ' selected' : '' ?>>Oldest</option>
                <option value="title"<?= $sort === 'title' ? ' selected' : '' ?>>Title A–Z</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </section>

    <?php if ($posts !== [] || $categories !== [] || $searchQ !== ''): ?>
        <section class="wrap" aria-label="Browse by category">
            <div class="pg-news-filters" role="tablist" aria-label="News categories">
                <?php foreach ($categories as $cat): ?>
                    <?php
                    $slug = (string) ($cat['slug'] ?? '');
                    $isAll = $slug === 'all-updates' || $slug === '';
                    $active = $isAll;
                    ?>
                    <a
                        href="#pg-news-grid"
                        class="pg-news-filter<?= $active ? ' is-active' : '' ?>"
                        data-news-filter="<?= h($isAll ? 'all-updates' : $slug) ?>"
                        role="tab"
                        aria-selected="<?= $active ? 'true' : 'false' ?>"
                    >
                        <i class="<?= h((string) ($cat['icon'] ?? 'fa-solid fa-newspaper')) ?>" aria-hidden="true"></i>
                        <?= h((string) ($cat['name'] ?? 'Updates')) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="wrap" id="pg-news-grid" aria-label="All news">
        <?php if ($posts === []): ?>
            <?php if ($featured !== null): ?>
                <div class="pg-news-empty" data-news-reveal>
                    More stories will appear in the grid as they are published.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="pg-news-grid">
                <?php foreach ($posts as $i => $post): ?>
                    <?php
                    if ($featured !== null && (int) $post['id'] === (int) $featured['id']) {
                        continue;
                    }
                    $img = trim((string) ($post['image_path'] ?? ''));
                    if ($img === '') {
                        $img = 'public/images/eastline.jpg';
                    }
                    $catName = trim((string) ($post['category_name'] ?? $post['kicker'] ?? 'Update'));
                    $catSlug = (string) ($post['category_slug'] ?? '');
                    $date = formatNewsDate((string) ($post['published_at'] ?? $post['created_at'] ?? ''));
                    $read = (int) ($post['reading_time'] ?? 1);
                    ?>
                    <article class="pg-news-card" data-news-reveal data-news-card data-category="<?= h($catSlug) ?>">
                        <div class="pg-news-card-media">
                            <?php if ($catName !== ''): ?>
                                <span class="pg-news-badge"><?= h($catName) ?></span>
                            <?php endif; ?>
                            <a href="<?= h((string) $post['url']) ?>">
                                <img src="<?= h(webPath($img)) ?>" alt="<?= h((string) ($post['image_alt'] ?? $post['title'])) ?>" width="640" height="480" loading="lazy" decoding="async">
                            </a>
                        </div>
                        <div class="pg-news-card-body">
                            <h3><a href="<?= h((string) $post['url']) ?>"><?= h((string) $post['title']) ?></a></h3>
                            <?php if (trim((string) ($post['summary'] ?? '')) !== ''): ?>
                                <p><?= h((string) $post['summary']) ?></p>
                            <?php endif; ?>
                            <div class="pg-news-card-foot">
                                <div class="pg-news-meta">
                                    <?php if ($date !== ''): ?><span><?= h($date) ?></span><?php endif; ?>
                                    <span><?= $read ?> min read</span>
                                </div>
                                <a class="pg-news-card-arrow" href="<?= h((string) $post['url']) ?>" aria-label="Read <?= h((string) $post['title']) ?>">
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="pg-news-empty" data-news-empty-filter hidden>No stories in this category yet.</div>
            <?php if ($totalPages > 1): ?>
                <nav class="pg-news-filters" aria-label="News pagination" style="padding-top:8px;">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php
                        $qs = http_build_query(array_filter([
                            'q' => $searchQ !== '' ? $searchQ : null,
                            'sort' => $sort !== 'newest' ? $sort : null,
                            'page' => $p > 1 ? $p : null,
                        ]));
                        $href = webPath('pages/news') . ($qs !== '' ? '?' . $qs : '');
                        ?>
                        <a class="pg-news-filter<?= $p === $page ? ' is-active' : '' ?>" href="<?= h($href) ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <section class="wrap" aria-label="Newsletter">
        <div class="pg-news-newsletter" data-news-reveal>
            <div class="pg-news-newsletter-inner">
                <div class="pg-news-newsletter-icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></div>
                <div>
                    <h2>Stay Updated</h2>
                    <p>Get the latest property news, launches and exclusive offers delivered to your inbox.</p>
                </div>
                <div>
                    <form class="pg-news-newsletter-form" method="post" action="<?= h(webPath('actions/newsletter_subscribe.php')) ?>" data-news-newsletter>
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <label class="visually-hidden" for="pg-news-email">Email address</label>
                        <input id="pg-news-email" type="email" name="email" required maxlength="190" placeholder="Enter your email address" autocomplete="email">
                        <button type="submit">Subscribe</button>
                    </form>
                    <p class="pg-news-newsletter-msg" data-news-newsletter-msg role="status" aria-live="polite"></p>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
