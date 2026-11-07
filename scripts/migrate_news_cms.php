<?php
declare(strict_types=1);

/**
 * Extends news_posts, adds news_categories + newsletter_subscribers,
 * seeds default categories and DEMO DRAFT articles.
 *
 * Run: php scripts/migrate_news_cms.php
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/content.php';

$pdo = getDatabaseConnection();
ensureContentTables();

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function tableExistsLocal(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

echo "Migrating news CMS…\n";

if (!tableExistsLocal($pdo, 'news_categories')) {
    $pdo->exec(
        "CREATE TABLE news_categories (
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
    echo "Created news_categories\n";
} else {
    echo "news_categories exists\n";
}

if (!tableExistsLocal($pdo, 'newsletter_subscribers')) {
    $pdo->exec(
        "CREATE TABLE newsletter_subscribers (
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
    echo "Created newsletter_subscribers\n";
} else {
    echo "newsletter_subscribers exists\n";
}

$alterCols = [
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

foreach ($alterCols as $col => $def) {
    if (!columnExists($pdo, 'news_posts', $col)) {
        $pdo->exec("ALTER TABLE news_posts ADD COLUMN {$col} {$def}");
        echo "Added news_posts.{$col}\n";
    }
}

// Widen body for long-form editorial
try {
    $pdo->exec('ALTER TABLE news_posts MODIFY body MEDIUMTEXT NULL');
} catch (Throwable $e) {
    // ignore if already mediumtext / permission
}

$categories = [
    ['All Updates', 'all-updates', 'fa-solid fa-layer-group', 0],
    ['New Launches', 'new-launches', 'fa-solid fa-building', 10],
    ['Market Insights', 'market-insights', 'fa-solid fa-chart-line', 20],
    ['RERA & Policy', 'rera-policy', 'fa-solid fa-scale-balanced', 30],
    ['Offers', 'offers', 'fa-solid fa-tags', 40],
    ['Company News', 'company-news', 'fa-solid fa-briefcase', 50],
    ['Events & Exhibitions', 'events-exhibitions', 'fa-solid fa-calendar-days', 60],
    ['Real Estate Tips', 'real-estate-tips', 'fa-solid fa-lightbulb', 70],
];

$catStmt = $pdo->prepare(
    'INSERT INTO news_categories (name, slug, icon, sort_order, is_active)
     SELECT ?, ?, ?, ?, 1 FROM DUAL
     WHERE NOT EXISTS (SELECT 1 FROM news_categories WHERE slug = ?)'
);
foreach ($categories as [$name, $slug, $icon, $sort]) {
    $catStmt->execute([$name, $slug, $icon, $sort, $slug]);
}
echo "Categories seeded\n";

$catIds = [];
foreach ($pdo->query('SELECT id, slug FROM news_categories')->fetchAll() as $row) {
    $catIds[(string) $row['slug']] = (int) $row['id'];
}

$seedCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM news_posts WHERE title LIKE '[DEMO]%'"
)->fetchColumn();

if ($seedCount === 0) {
    $seeds = [
        [
            'title' => '[DEMO] Exploring premium residential opportunities in Pune',
            'slug' => 'demo-premium-residential-pune',
            'category' => 'new-launches',
            'layout' => 'spotlight',
            'kicker' => 'New Launches',
            'summary' => 'A desk note on how PropGemini reviews Pune residences before recommending a site visit.',
            'body' => "DEMO DRAFT — sample editorial only. This note does not announce a live launch or price.\n\nPropGemini reviews published inventory across Pune with attention to location, configuration and developer track record.\n\nSpeak with an advisor for current availability and verified project details.",
            'highlight' => null,
            'image' => 'public/images/projects/pune-tower-1.jpg',
            'featured' => 1,
            'is_highlight' => 1,
            'highlight_sort' => 1,
            'reading' => 3,
        ],
        [
            'title' => '[DEMO] Mumbai residences: what buyers ask before a visit',
            'slug' => 'demo-mumbai-residences-site-visit',
            'category' => 'market-insights',
            'layout' => 'editorial',
            'kicker' => 'Market Insights',
            'summary' => 'A short primer on questions Mumbai buyers typically raise with PropGemini advisors.',
            'body' => "DEMO DRAFT — illustrative only. No market growth figures or discounts are claimed.\n\nTypical questions include configuration fit, commute, and how to prepare for a site visit.\n\nAlways verify project status and documentation with the developer and a PropGemini advisor.",
            'highlight' => null,
            'image' => 'public/images/projects/mumbai-tower-1.jpg',
            'featured' => 0,
            'is_highlight' => 1,
            'highlight_sort' => 2,
            'reading' => 4,
        ],
        [
            'title' => '[DEMO] Gurgaon desk notes for residential enquiries',
            'slug' => 'demo-gurgaon-desk-notes',
            'category' => 'market-insights',
            'layout' => 'editorial',
            'kicker' => 'Market Insights',
            'summary' => 'How PropGemini frames Gurgaon residential conversations without inventing market statistics.',
            'body' => "DEMO DRAFT — sample content.\n\nGurgaon enquiries often focus on connectivity, configuration and timing of a visit.\n\nPropGemini shares only verified inventory details from published listings.",
            'highlight' => null,
            'image' => 'public/images/projects/gurgaon-tower-1.jpg',
            'featured' => 0,
            'is_highlight' => 1,
            'highlight_sort' => 3,
            'reading' => 3,
        ],
        [
            'title' => '[DEMO] A practical property buying checklist',
            'slug' => 'demo-property-buying-checklist',
            'category' => 'real-estate-tips',
            'layout' => 'editorial',
            'kicker' => 'Real Estate Tips',
            'summary' => 'General checklist themes to discuss with your advisor before committing to a purchase.',
            'body' => "DEMO DRAFT — educational outline only.\n\nConsider documentation, site visit observations, financing readiness and developer disclosures.\n\nThis is not legal advice. Confirm every point with qualified professionals.",
            'highlight' => null,
            'image' => 'public/images/insight-pune.jpg',
            'featured' => 0,
            'is_highlight' => 0,
            'highlight_sort' => 0,
            'reading' => 5,
        ],
        [
            'title' => '[DEMO] Understanding RERA conversations as a homebuyer',
            'slug' => 'demo-rera-homebuyer-notes',
            'category' => 'rera-policy',
            'layout' => 'editorial',
            'kicker' => 'RERA & Policy',
            'summary' => 'High-level themes homebuyers often discuss — not a substitute for official RERA records.',
            'body' => "DEMO DRAFT — no project-specific RERA approvals are asserted here.\n\nAsk your advisor how to review official project disclosures and registration details.\n\nAlways rely on government and developer source documents.",
            'highlight' => null,
            'image' => 'public/images/insight-mumbai.jpg',
            'featured' => 0,
            'is_highlight' => 0,
            'highlight_sort' => 0,
            'reading' => 4,
        ],
        [
            'title' => '[DEMO] PropGemini desk update',
            'slug' => 'demo-propgemini-desk-update',
            'category' => 'company-news',
            'layout' => 'note',
            'kicker' => 'Company News',
            'summary' => 'A compact sample announcement for company updates.',
            'body' => "DEMO DRAFT — placeholder company note.\n\nReplace this with a real PropGemini update when you are ready to publish.",
            'highlight' => 'Internal sample',
            'image' => null,
            'featured' => 0,
            'is_highlight' => 0,
            'highlight_sort' => 0,
            'reading' => 1,
        ],
        [
            'title' => '[DEMO] Sample offer layout (not a live promotion)',
            'slug' => 'demo-sample-offer-layout',
            'category' => 'offers',
            'layout' => 'offer',
            'kicker' => 'Offers',
            'summary' => 'Demonstrates the Offer layout. No discount or price claim is made.',
            'body' => "DEMO DRAFT — do not treat this as an active offer.\n\nWhen publishing a real offer, add verified terms, validity and developer confirmation.",
            'highlight' => 'Sample callout only',
            'image' => 'public/images/projects/pool-amenity.jpg',
            'featured' => 0,
            'is_highlight' => 0,
            'highlight_sort' => 0,
            'reading' => 2,
        ],
        [
            'title' => '[DEMO] Site visit readiness notes',
            'slug' => 'demo-site-visit-readiness',
            'category' => 'events-exhibitions',
            'layout' => 'spotlight',
            'kicker' => 'Events & Exhibitions',
            'summary' => 'How PropGemini prepares visitors for a scheduled site tour.',
            'body' => "DEMO DRAFT — general guidance only.\n\nSite-visit slots depend on project access rules and confirmation by the desk.\n\nBring identification and any questions you want answered on site.",
            'highlight' => null,
            'image' => 'public/images/projects/skyline-dusk.jpg',
            'featured' => 0,
            'is_highlight' => 0,
            'highlight_sort' => 0,
            'reading' => 3,
        ],
    ];

    $ins = $pdo->prepare(
        'INSERT INTO news_posts
         (title, slug, kicker, summary, body, highlight, image_path, layout, status, published_at,
          category_id, is_featured, is_highlight, highlight_sort, reading_time, author, image_alt)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );

    foreach ($seeds as $seed) {
        $catId = $catIds[$seed['category']] ?? null;
        $ins->execute([
            $seed['title'],
            $seed['slug'],
            $seed['kicker'],
            $seed['summary'],
            $seed['body'],
            $seed['highlight'],
            $seed['image'],
            $seed['layout'],
            'Draft',
            null,
            $catId,
            $seed['featured'],
            $seed['is_highlight'],
            $seed['highlight_sort'],
            $seed['reading'],
            'PropGemini Desk',
            $seed['title'],
        ]);
    }
    echo "Seeded " . count($seeds) . " DEMO DRAFT articles\n";
} else {
    echo "DEMO drafts already present ({$seedCount})\n";
}

echo "DONE\n";
