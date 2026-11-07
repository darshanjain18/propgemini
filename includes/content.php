<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__) . '/config/database.php';

function ensureContentTables(): void
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
            body TEXT DEFAULT NULL,
            highlight VARCHAR(120) DEFAULT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            layout ENUM('spotlight','offer','editorial','note') NOT NULL DEFAULT 'editorial',
            status ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
            published_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY status_published (status, published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS gallery_items (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            caption VARCHAR(400) DEFAULT NULL,
            media_type ENUM('image','video') NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            status ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY status_sort (status, sort_order, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function newsLayouts(): array
{
    return [
        'spotlight' => 'Property spotlight — image beside the story',
        'offer' => 'Offer — price, discount or launch callout',
        'editorial' => 'Article — a longer read',
        'note' => 'Short note — a brief update',
    ];
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
            'highlight' => 'From ₹2.4 crore · limited release',
            'body' => "The offer line sits above the story so the price or discount is the first thing a reader sees.",
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

function galleryLayoutSamples(): array
{
    return [
        [
            'title' => 'Residence at dusk',
            'caption' => 'Sample photograph',
            'media_type' => 'image',
            'file_path' => 'public/images/hero.jpg',
        ],
        [
            'title' => 'Courtyard',
            'caption' => 'Sample photograph',
            'media_type' => 'image',
            'file_path' => 'public/images/canopy.jpg',
        ],
        [
            'title' => 'A short walk-through',
            'caption' => 'Sample film',
            'media_type' => 'video',
            'file_path' => 'public/videos/canopy-tour.mp4',
        ],
    ];
}

function renderGalleryCard(array $item): void
{
    ?>
    <figure class="gallery-card">
        <?php if (($item['media_type'] ?? '') === 'video'): ?>
            <video controls playsinline preload="metadata" src="<?= h(webPath((string) $item['file_path'])) ?>"></video>
        <?php else: ?>
            <img src="<?= h(webPath((string) $item['file_path'])) ?>" alt="<?= h((string) $item['title']) ?>" loading="lazy">
        <?php endif; ?>
        <figcaption>
            <strong><?= h((string) $item['title']) ?></strong>
            <?php if (trim((string) ($item['caption'] ?? '')) !== ''): ?>
                <span><?= h((string) $item['caption']) ?></span>
            <?php endif; ?>
        </figcaption>
    </figure>
    <?php
}

function newsLayoutLabel(string $layout): string
{
    return match ($layout) {
        'spotlight' => 'Property spotlight',
        'offer' => 'Offer',
        'note' => 'Short note',
        default => 'Article',
    };
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

function publishedNews(): array
{
    ensureContentTables();
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(
        "SELECT * FROM news_posts
         WHERE status = 'Published'
         ORDER BY COALESCE(published_at, created_at) DESC, id DESC"
    );

    return $stmt->fetchAll();
}

function publishedGallery(): array
{
    ensureContentTables();
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(
        "SELECT * FROM gallery_items
         WHERE status = 'Published'
         ORDER BY sort_order ASC, id DESC"
    );

    return $stmt->fetchAll();
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
    $kicker = trim((string) ($item['kicker'] ?? ''));
    $highlight = trim((string) ($item['highlight'] ?? ''));
    ?>
    <article class="news-item news-<?= h($layout) ?>">
        <?php if ($layout === 'editorial' && $kicker !== ''): ?>
            <p class="news-kicker"><?= h($kicker) ?></p>
        <?php endif; ?>
        <?php if ($layout === 'editorial'): ?>
            <h2><?= h((string) $item['title']) ?></h2>
        <?php endif; ?>
        <?php if ($image !== '' && $layout !== 'note'): ?>
            <img src="<?= h(webPath($image)) ?>" alt="<?= h((string) $item['title']) ?>">
        <?php endif; ?>
        <div class="news-copy">
            <?php if ($layout !== 'editorial' && $kicker !== ''): ?>
                <p class="news-kicker"><?= h($kicker) ?></p>
            <?php endif; ?>
            <?php if ($highlight !== '' && $layout === 'offer'): ?>
                <p class="news-highlight"><?= h($highlight) ?></p>
            <?php endif; ?>
            <?php if ($layout !== 'editorial'): ?>
                <h2><?= h((string) $item['title']) ?></h2>
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
