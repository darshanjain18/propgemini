<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/content.php';

$pageTitle = 'Gallery | PropGemini';
$pageDescription = 'Photographs and films from PropGemini homes and workspaces.';
$bodyClass = 'gallery-page';
$pageStyles = ['public/css/gallery.css', 'public/css/masonry.css'];
$pageScripts = ['public/js/masonry.js'];

$galleryHeroImage = 'public/images/projects/pune-tower-1.jpg';
if (!is_file(dirname(__DIR__) . '/' . $galleryHeroImage)) {
    $galleryHeroImage = 'public/images/horizon.jpg';
}
$galleryItems = array_values(array_filter(
    publishedGallery(),
    static function (array $item): bool {
        $path = trim((string) ($item['file_path'] ?? ''));
        if ($path === '' || $path === '/') {
            return false;
        }
        $resolved = webPath($path);

        return $resolved !== '/' && $resolved !== '' && !str_ends_with($resolved, '//');
    }
));
if ($galleryItems === []) {
    // Demonstration tiles until published gallery uploads are available.
    $galleryItems = [
        ['id' => 1, 'title' => 'Residence at dusk', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/hero.jpg'],
        ['id' => 2, 'title' => 'Courtyard light', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/canopy.jpg'],
        ['id' => 3, 'title' => 'Quiet interior', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/meridian.jpg'],
        ['id' => 4, 'title' => 'City outlook', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/insight-pune.jpg'],
        ['id' => 5, 'title' => 'Harbour evening', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/insight-mumbai.jpg'],
        ['id' => 6, 'title' => 'Private villa', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/villas.jpg'],
        ['id' => 7, 'title' => 'Horizon view', 'caption' => 'Sample photograph', 'media_type' => 'image', 'file_path' => 'public/images/horizon.jpg'],
        ['id' => 8, 'title' => 'Walk-through film', 'caption' => 'Sample film', 'media_type' => 'video', 'file_path' => 'public/videos/canopy-tour.mp4'],
    ];
}

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <section class="pg-gallery-hero-section" aria-labelledby="pg-gallery-hero-heading">
        <div class="wrap">
            <div class="pg-gallery-hero">
                <svg class="pg-gallery-hero-waves" viewBox="0 0 900 320" fill="none" aria-hidden="true" focusable="false">
                    <path class="pg-gallery-hero-wave pg-gallery-hero-wave--left" d="M20 250C110 190 180 150 270 170C380 196 430 270 540 250C650 230 700 140 860 120" stroke="currentColor" stroke-width="1.4"/>
                    <path class="pg-gallery-hero-wave pg-gallery-hero-wave--left" d="M10 285C105 235 175 205 265 220C375 240 440 300 550 275C660 250 730 165 880 145" stroke="currentColor" stroke-width="1" opacity="0.55"/>
                    <path class="pg-gallery-hero-wave pg-gallery-hero-wave--mid" d="M420 40C500 70 560 120 620 110C700 96 740 40 820 55" stroke="currentColor" stroke-width="1.2" opacity="0.65"/>
                </svg>
                <div class="pg-gallery-hero-copy">
                    <p class="pg-gallery-hero-kicker">Gallery &amp; Collections</p>
                    <h1 id="pg-gallery-hero-heading">Explore Spaces That <em>Inspire.</em></h1>
                    <p class="pg-gallery-hero-lead">A visual journey through exceptional residences, refined interiors and thoughtfully designed spaces.</p>
                </div>
                <figure class="pg-gallery-hero-visual">
                    <img
                        src="<?= h(webPath($galleryHeroImage)) ?>"
                        alt="Luxury residential architecture at dusk"
                        width="960"
                        height="720"
                        decoding="async"
                    >
                </figure>
            </div>
        </div>
    </section>

    <section class="pg-section gallery-masonry-wrap">
        <div class="wrap">
            <?php if ($galleryItems === []): ?>
                <p class="pg-empty">The gallery will fill in as photographs and films are published.</p>
            <?php else: ?>
                <div
                    class="masonry"
                    id="galleryMasonry"
                    data-masonry
                    data-columns="auto"
                    data-column-width="280"
                    data-gap="16"
                    data-radius="16"
                    data-animate-from="bottom"
                    data-stagger="0.05"
                    data-duration="0.6"
                    data-bounce="0.15"
                    data-blur-to-focus="true"
                    data-reveal-on-scroll="true"
                    data-hover-effect="zoom"
                    data-captions="hover"
                    data-parallax="0.12"
                    data-lightbox="true"
                    role="list"
                >
                    <?php foreach ($galleryItems as $index => $item): ?>
                        <?php
                        $id = (string) ((int) ($item['id'] ?? ($index + 1)));
                        $type = (($item['media_type'] ?? '') === 'video') ? 'video' : 'image';
                        $src = webPath((string) ($item['file_path'] ?? ''));
                        $title = (string) ($item['title'] ?? '');
                        $caption = trim((string) ($item['caption'] ?? ''));
                        $alt = $title !== '' ? $title : 'Gallery media ' . ($index + 1);
                        ?>
                        <div
                            class="masonry__item"
                            data-id="<?= h($id) ?>"
                            data-type="<?= h($type) ?>"
                            data-src="<?= h($src) ?>"
                            data-title="<?= h($title) ?>"
                            data-description="<?= h($caption) ?>"
                            data-alt="<?= h($alt) ?>"
                            role="listitem"
                        >
                            <button class="masonry__card" type="button" data-card aria-label="<?= h($alt) ?>">
                                <span class="masonry__media" data-media>
                                    <span class="masonry__parallax" data-parallax>
                                        <?php if ($type === 'video'): ?>
                                            <video
                                                class="masonry__video"
                                                muted
                                                playsinline
                                                preload="metadata"
                                                src="<?= h($src) ?>"
                                            ></video>
                                        <?php else: ?>
                                            <img
                                                class="masonry__image"
                                                src="<?= h($src) ?>"
                                                alt="<?= h($alt) ?>"
                                                loading="lazy"
                                                decoding="async"
                                                draggable="false"
                                            >
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($type === 'video'): ?>
                                        <span class="masonry__badge">Video</span>
                                    <?php endif; ?>
                                    <?php if ($title !== '' || $caption !== ''): ?>
                                        <span class="masonry__caption" data-mode="hover">
                                            <?php if ($title !== ''): ?>
                                                <span class="masonry__title"><?= h($title) ?></span>
                                            <?php endif; ?>
                                            <?php if ($caption !== ''): ?>
                                                <span class="masonry__text"><?= h($caption) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
