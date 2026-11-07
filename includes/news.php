<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__) . '/config/database.php';

function ensureNewsSchema(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    $pdo = getDatabaseConnection();

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS news_posts (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            slug VARCHAR(190) NOT NULL UNIQUE,
            kicker VARCHAR(40) DEFAULT NULL,
            summary VARCHAR(500) DEFAULT NULL,
            body MEDIUMTEXT DEFAULT NULL,
            highlight VARCHAR(120) DEFAULT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            layout ENUM('spotlight','offer','editorial','note') NOT NULL DEFAULT 'editorial',
            status ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
            published_at DATETIME DEFAULT NULL,
            category_id INT NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            is_highlight TINYINT(1) NOT NULL DEFAULT 0,
            highlight_sort INT NOT NULL DEFAULT 0,
            reading_time SMALLINT UNSIGNED NULL,
            author VARCHAR(120) NULL,
            image_alt VARCHAR(180) NULL,
            image_caption VARCHAR(255) NULL,
            gallery_json TEXT NULL,
            video_url VARCHAR(500) NULL,
            seo_title VARCHAR(180) NULL,
            seo_description VARCHAR(320) NULL,
            social_image_path VARCHAR(255) NULL,
            cta_text VARCHAR(80) NULL,
            cta_url VARCHAR(255) NULL,
            related_property_id INT NULL,
            offer_terms VARCHAR(500) NULL,
            expires_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY status_published (status, published_at),
            KEY featured (is_featured),
            KEY highlight_sort (is_highlight, highlight_sort)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS news_categories (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(80) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            icon VARCHAR(60) NOT NULL DEFAULT 'fa-solid fa-newspaper',
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY active_sort (is_active, sort_order, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL UNIQUE,
            status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
            source VARCHAR(60) NOT NULL DEFAULT 'news_page',
            ip_address VARCHAR(45) DEFAULT NULL,
            subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at DATETIME DEFAULT NULL,
            KEY status_subscribed (status, subscribed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $cols = [
        'category_id' => 'INT NULL',
        'is_featured' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'is_highlight' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'highlight_sort' => 'INT NOT NULL DEFAULT 0',
        'reading_time' => 'SMALLINT UNSIGNED NULL',
        'author' => 'VARCHAR(120) NULL',
        'image_alt' => 'VARCHAR(180) NULL',
        'image_caption' => 'VARCHAR(255) NULL',
        'gallery_json' => 'TEXT NULL',
        'video_url' => 'VARCHAR(500) NULL',
        'seo_title' => 'VARCHAR(180) NULL',
        'seo_description' => 'VARCHAR(320) NULL',
        'social_image_path' => 'VARCHAR(255) NULL',
        'cta_text' => 'VARCHAR(80) NULL',
        'cta_url' => 'VARCHAR(255) NULL',
        'related_property_id' => 'INT NULL',
        'offer_terms' => 'VARCHAR(500) NULL',
        'expires_at' => 'DATETIME NULL',
    ];

    foreach ($cols as $col => $def) {
        $check = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $check->execute(['news_posts', $col]);
        if ((int) $check->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE news_posts ADD COLUMN {$col} {$def}");
        }
    }
}

function newsLayouts(): array
{
    return [
        'spotlight' => 'Property spotlight — image beside the story',
        'offer' => 'Offer — promotional story with callout',
        'editorial' => 'Editorial article — longer read',
        'note' => 'Short note — compact update',
    ];
}

function newsLayoutLabel(string $layout): string
{
    return match ($layout) {
        'spotlight' => 'Property spotlight',
        'offer' => 'Offer',
        'note' => 'Short note',
        default => 'Editorial article',
    };
}

function newsLayoutSamples(): array
{
    return [
        'spotlight' => [
            'layout' => 'spotlight',
            'kicker' => 'New launch',
            'title' => 'A new residence on the Pune riverfront',
            'summary' => 'Three and four bedroom homes with a private deck and a quiet courtyard.',
            'highlight' => 'Bookings open this month',
            'body' => "The first release is a limited set of residences.\n\nA site visit can be arranged through the desk.",
            'image_path' => 'public/images/hero.jpg',
        ],
        'offer' => [
            'layout' => 'offer',
            'kicker' => 'Offer',
            'title' => 'Introductory pricing on a Mumbai residence',
            'summary' => 'A short window for buyers who are ready to reserve this month.',
            'highlight' => 'Verified terms apply',
            'body' => 'The offer line sits above the story so the callout is the first thing a reader sees.',
            'image_path' => 'public/images/meridian.jpg',
        ],
        'editorial' => [
            'layout' => 'editorial',
            'kicker' => 'Market note',
            'title' => 'What buyers are asking in Gurgaon this season',
            'summary' => 'A longer read for a price update, a new phase, or a neighbourhood note.',
            'highlight' => 'Sample article',
            'body' => "This layout leads with the headline, then the photograph, then the full note.\n\nUse it when the story needs more than a single line.",
            'image_path' => 'public/images/horizon.jpg',
        ],
        'note' => [
            'layout' => 'note',
            'kicker' => 'Update',
            'title' => 'Site visits resume next week',
            'summary' => 'A brief update with no photograph.',
            'highlight' => 'Pune · Mumbai · Gurgaon',
            'body' => 'Use the short note when the update is only a few lines.',
            'image_path' => '',
        ],
    ];
}

function uniqueNewsSlug(PDO $pdo, string $base, ?int $ignoreId = null): string
{
    $slug = strtolower(trim($base));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-') ?: 'news';
    $candidate = $slug;
    $i = 2;
    $stmt = $pdo->prepare('SELECT id FROM news_posts WHERE slug = ? LIMIT 1');

    while (true) {
        $stmt->execute([$candidate]);
        $found = $stmt->fetchColumn();
        if ($found === false || ($ignoreId !== null && (int) $found === $ignoreId)) {
            return $candidate;
        }
        $candidate = $slug . '-' . $i;
        $i++;
    }
}

function estimateNewsReadingTime(string $body, string $summary = ''): int
{
    $text = trim($summary . ' ' . $body);
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $minutes = (int) ceil(count($words) / 200);
    return max(1, min(60, $minutes));
}

function newsPublicSelectSql(string $extraWhere = '', string $order = 'COALESCE(n.published_at, n.created_at) DESC, n.id DESC'): string
{
    $where = "n.status = 'Published'
        AND (n.published_at IS NULL OR n.published_at <= NOW())
        AND (n.expires_at IS NULL OR n.expires_at > NOW())";
    if ($extraWhere !== '') {
        $where .= ' AND (' . $extraWhere . ')';
    }

    return "SELECT n.*,
                c.name AS category_name,
                c.slug AS category_slug,
                c.icon AS category_icon
         FROM news_posts n
         LEFT JOIN news_categories c ON c.id = n.category_id
         WHERE {$where}
         ORDER BY {$order}";
}

function hydrateNewsPost(array $row): array
{
    $gallery = [];
    $raw = trim((string) ($row['gallery_json'] ?? ''));
    if ($raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $gallery = array_values(array_filter(array_map('strval', $decoded)));
        }
    }

    $row['gallery'] = $gallery;
    $row['reading_time'] = (int) ($row['reading_time'] ?? 0);
    if ($row['reading_time'] <= 0) {
        $row['reading_time'] = estimateNewsReadingTime(
            (string) ($row['body'] ?? ''),
            (string) ($row['summary'] ?? '')
        );
    }
    $row['is_featured'] = (int) ($row['is_featured'] ?? 0) === 1;
    $row['is_highlight'] = (int) ($row['is_highlight'] ?? 0) === 1;
    $row['url'] = newsArticleUrl((string) ($row['slug'] ?? ''));
    return $row;
}

function newsArticleUrl(string $slug): string
{
    $slug = trim($slug);
    if ($slug === '') {
        return webPath('pages/news');
    }
    return webPath('news/' . rawurlencode($slug));
}

function publishedNews(?string $categorySlug = null, int $limit = 0, int $offset = 0): array
{
    ensureNewsSchema();
    $pdo = getDatabaseConnection();
    $extra = '';
    $params = [];
    if ($categorySlug !== null && $categorySlug !== '' && $categorySlug !== 'all-updates') {
        $extra = 'c.slug = ?';
        $params[] = $categorySlug;
    }

    $sql = newsPublicSelectSql($extra);
    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return array_map('hydrateNewsPost', $stmt->fetchAll());
}

function countPublishedNews(?string $categorySlug = null): int
{
    ensureNewsSchema();
    $pdo = getDatabaseConnection();
    $sql = "SELECT COUNT(*) FROM news_posts n
            LEFT JOIN news_categories c ON c.id = n.category_id
            WHERE n.status = 'Published'
              AND (n.published_at IS NULL OR n.published_at <= NOW())
              AND (n.expires_at IS NULL OR n.expires_at > NOW())";
    $params = [];
    if ($categorySlug !== null && $categorySlug !== '' && $categorySlug !== 'all-updates') {
        $sql .= ' AND c.slug = ?';
        $params[] = $categorySlug;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function featuredNewsPost(): ?array
{
    ensureNewsSchema();
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(newsPublicSelectSql('n.is_featured = 1') . ' LIMIT 1');
    $row = $stmt->fetch();
    if ($row === false) {
        $fallback = publishedNews(null, 1);
        return $fallback[0] ?? null;
    }
    return hydrateNewsPost($row);
}

function highlightNewsPosts(int $limit = 3): array
{
    ensureNewsSchema();
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(
        newsPublicSelectSql('n.is_highlight = 1', 'n.highlight_sort ASC, COALESCE(n.published_at, n.created_at) DESC')
        . ' LIMIT ' . max(1, $limit)
    );
    $rows = array_map('hydrateNewsPost', $stmt->fetchAll());
    if ($rows !== []) {
        return $rows;
    }
    $featured = featuredNewsPost();
    $latest = publishedNews(null, $limit + 1);
    $out = [];
    foreach ($latest as $item) {
        if ($featured !== null && (int) $item['id'] === (int) $featured['id']) {
            continue;
        }
        $out[] = $item;
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

function newsPostBySlug(string $slug): ?array
{
    ensureNewsSchema();
    $slug = trim($slug);
    if ($slug === '') {
        return null;
    }
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(newsPublicSelectSql('n.slug = ?') . ' LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row === false ? null : hydrateNewsPost($row);
}

function newsPostById(int $id): ?array
{
    ensureNewsSchema();
    if ($id <= 0) {
        return null;
    }
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'SELECT n.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
         FROM news_posts n
         LEFT JOIN news_categories c ON c.id = n.category_id
         WHERE n.id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row === false ? null : hydrateNewsPost($row);
}

function activeNewsCategories(bool $includeAll = true): array
{
    ensureNewsSchema();
    $pdo = getDatabaseConnection();
    $rows = $pdo->query(
        'SELECT * FROM news_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
    )->fetchAll();

    if (!$includeAll) {
        return array_values(array_filter($rows, static fn ($r) => (string) $r['slug'] !== 'all-updates'));
    }
    return $rows;
}

function allNewsCategories(): array
{
    ensureNewsSchema();
    $pdo = getDatabaseConnection();
    return $pdo->query('SELECT * FROM news_categories ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function formatNewsDate(?string $datetime): string
{
    if ($datetime === null || trim($datetime) === '') {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts === false ? '' : date('d M Y', $ts);
}

function renderNewsBody(string $body): void
{
    $chunks = preg_split("/\R{2,}/", trim($body)) ?: [];
    foreach ($chunks as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '') {
            continue;
        }
        echo '<p>' . nl2br(h($chunk)) . '</p>';
    }
}

function renderNewsItem(array $item): void
{
    $layout = (string) ($item['layout'] ?? 'editorial');
    if (!isset(newsLayouts()[$layout])) {
        $layout = 'editorial';
    }
    $image = trim((string) ($item['image_path'] ?? ''));
    $kicker = trim((string) ($item['kicker'] ?? ($item['category_name'] ?? '')));
    $highlight = trim((string) ($item['highlight'] ?? ''));
    $url = (string) ($item['url'] ?? newsArticleUrl((string) ($item['slug'] ?? '')));
    ?>
    <article class="news-item news-<?= h($layout) ?>">
        <?php if ($layout === 'editorial' && $kicker !== ''): ?>
            <p class="news-kicker"><?= h($kicker) ?></p>
        <?php endif; ?>
        <?php if ($layout === 'editorial'): ?>
            <h2><a href="<?= h($url) ?>"><?= h((string) $item['title']) ?></a></h2>
        <?php endif; ?>
        <?php if ($image !== '' && $layout !== 'note'): ?>
            <a href="<?= h($url) ?>"><img src="<?= h(webPath($image)) ?>" alt="<?= h((string) ($item['image_alt'] ?? $item['title'])) ?>"></a>
        <?php endif; ?>
        <div class="news-copy">
            <?php if ($layout !== 'editorial' && $kicker !== ''): ?>
                <p class="news-kicker"><?= h($kicker) ?></p>
            <?php endif; ?>
            <?php if ($highlight !== '' && $layout === 'offer'): ?>
                <p class="news-highlight"><?= h($highlight) ?></p>
            <?php endif; ?>
            <?php if ($layout !== 'editorial'): ?>
                <h2><a href="<?= h($url) ?>"><?= h((string) $item['title']) ?></a></h2>
            <?php endif; ?>
            <?php if (trim((string) ($item['summary'] ?? '')) !== ''): ?>
                <p><?= h((string) $item['summary']) ?></p>
            <?php endif; ?>
            <?php if ($highlight !== '' && $layout !== 'offer'): ?>
                <p class="news-highlight"><?= h($highlight) ?></p>
            <?php endif; ?>
            <?php renderNewsBody((string) ($item['body'] ?? '')); ?>
        </div>
    </article>
    <?php
}

function subscribeNewsletter(string $email, string $source = 'news_page', ?string $ip = null): array
{
    ensureNewsSchema();
    $email = strtolower(trim($email));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Please enter a valid email address.'];
    }
    if (mb_strlen($email) > 190) {
        return ['ok' => false, 'message' => 'That email address is too long.'];
    }

    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT id, status FROM newsletter_subscribers WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing !== false) {
        if ((string) $existing['status'] === 'active') {
            return ['ok' => true, 'message' => 'You are already subscribed.'];
        }
        $pdo->prepare(
            'UPDATE newsletter_subscribers
             SET status = ?, unsubscribed_at = NULL, subscribed_at = NOW(), source = ?, ip_address = ?
             WHERE id = ?'
        )->execute(['active', $source, $ip, (int) $existing['id']]);
        return ['ok' => true, 'message' => 'Welcome back — your subscription is active again.'];
    }

    $pdo->prepare(
        'INSERT INTO newsletter_subscribers (email, status, source, ip_address)
         VALUES (?, ?, ?, ?)'
    )->execute([$email, 'active', $source, $ip]);

    return ['ok' => true, 'message' => 'Thanks — you are on the list.'];
}
