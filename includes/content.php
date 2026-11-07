<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/news.php';

function ensureContentTables(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    ensureNewsSchema();

    $pdo = getDatabaseConnection();
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
