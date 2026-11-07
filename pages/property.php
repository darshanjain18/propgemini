<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/properties.php';
require_once dirname(__DIR__) . '/includes/analytics.php';
require_once dirname(__DIR__) . '/includes/visit-form.php';

$id = (string) ($_GET['id'] ?? '');
$property = propertyById($id);
if ($property === null) {
    http_response_code(404);
} elseif (!empty($property['db_id'])) {
    recordPropertyView((int) $property['db_id']);
}

$pageTitle = $property
    ? ((string) ($property['seo_title'] ?? '') !== '' ? (string) $property['seo_title'] : $property['name'] . ' | PropGemini')
    : 'Property | PropGemini';
$pageDescription = $property
    ? ((string) ($property['seo_description'] ?? '') !== '' ? (string) $property['seo_description'] : (string) ($property['summary'] ?? 'Property details'))
    : 'Property details';
$bodyClass = 'property-page';
$pageStyles = ['public/css/property-detail.css'];
$pageScripts = ['public/js/property-detail.js'];

$unlocked = $property && !empty($_SESSION['brochure_ok'][$property['id']]);
$mapEmbed = $property ? propertyMapEmbedUrl($property) : '';
$emiLoan = $property && ($property['price'] ?? 0) > 0 ? (int) $property['price'] * 100000 : 0;

$gallery = [];
$videos = [];
$amenities = [];
$unitTypes = [];
$heroSlides = [];
$highlights = [];
$overviewImage = '';
$builderText = '';
$hasBrochure = false;
$hasStatus = false;
$hasBuilder = false;
$hasPlans = false;
$hasAmenities = false;
$hasMedia = false;
$constructionStatus = '';

if ($property !== null) {
    $gallery = $property['gallery'] !== [] ? $property['gallery'] : (($property['image'] ?? '') !== '' ? [$property['image']] : []);
    $videos = $property['videos'] !== [] ? $property['videos'] : (($property['video'] ?? '') !== '' ? [$property['video']] : []);
    $amenities = array_values(array_filter(array_map('strval', $property['amenities_list'] ?? [])));
    $unitTypes = $property['unit_types'] ?? [];
    $builderText = trim((string) ($property['about_builder'] ?? ''));
    $hasBuilder = $builderText !== '';
    $hasBrochure = !empty($property['brochure_path']) || $unlocked;
    $constructionStatus = trim((string) ($property['construction_status'] !== '' ? $property['construction_status'] : $property['status']));
    $hasStatus = $constructionStatus !== ''
        || $property['construction_progress'] !== null
        || !empty($property['possession_date'])
        || !empty($property['launch_date']);
    $hasPlans = $unitTypes !== [] || (preg_match('/^(\d+) BHK$/', (string) $property['bhk']) === 1);
    $hasAmenities = $amenities !== [];
    $hasMedia = $gallery !== [] || $videos !== [];
    $highlights = array_slice($amenities, 0, 4);
    $overviewImage = (string) ($gallery[1] ?? $gallery[0] ?? $property['image'] ?? '');

    foreach ($gallery as $src) {
        $heroSlides[] = ['type' => 'image', 'src' => (string) $src];
    }
    if ($heroSlides === [] && ($property['image'] ?? '') !== '') {
        $heroSlides[] = ['type' => 'image', 'src' => (string) $property['image']];
    }
}

$tabs = [
    'overview' => 'Overview',
    'plans' => 'Floor Plans',
    'amenities' => 'Amenities',
    'location' => 'Location',
    'media' => 'Images/Videos',
    'builder' => 'About Builder',
    'status' => 'Status',
    'brochure' => 'Brochure',
];

$amenityIcon = static function (string $name): string {
    $key = strtolower($name);
    if (str_contains($key, 'pool') || str_contains($key, 'swim')) {
        return 'pool';
    }
    if (str_contains($key, 'gym') || str_contains($key, 'fitness')) {
        return 'gym';
    }
    if (str_contains($key, 'club')) {
        return 'club';
    }
    if (str_contains($key, 'garden') || str_contains($key, 'landscap') || str_contains($key, 'park')) {
        return 'garden';
    }
    if (str_contains($key, 'secur') || str_contains($key, 'cctv') || str_contains($key, 'guard')) {
        return 'security';
    }
    if (str_contains($key, 'kid') || str_contains($key, 'play') || str_contains($key, 'child')) {
        return 'kids';
    }
    if (str_contains($key, 'park')) {
        return 'parking';
    }
    return 'star';
};

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="pg-pd">
    <?php if ($property === null): ?>
        <section class="section wrap pg-pd-missing">
            <h1>This property could not be found.</h1>
            <a class="text-link" href="<?= h(webPath('pages/properties')) ?>">Back to projects ↗</a>
        </section>
    <?php else: ?>
        <section class="pg-pd-hero" data-pd-hero aria-label="<?= h($property['name']) ?> gallery">
            <div class="pg-pd-hero-media" data-pd-hero-track>
                <?php foreach ($heroSlides as $index => $slide): ?>
                    <figure class="pg-pd-hero-slide<?= $index === 0 ? ' is-active' : '' ?>" data-pd-slide="<?= $index ?>">
                        <img src="<?= h(webPath($slide['src'])) ?>" alt="<?= h($property['name']) ?> image <?= $index + 1 ?>" <?= $index === 0 ? '' : 'loading="lazy"' ?>>
                    </figure>
                <?php endforeach; ?>
                <?php if ($heroSlides === []): ?>
                    <figure class="pg-pd-hero-slide is-active">
                        <img src="<?= h(webPath('public/images/hero.jpg')) ?>" alt="<?= h($property['name']) ?>">
                    </figure>
                <?php endif; ?>
            </div>
            <div class="pg-pd-hero-veil" aria-hidden="true"></div>

            <div class="wrap pg-pd-hero-grid">
                <div class="pg-pd-hero-copy">
                    <p class="pg-pd-location">
                        <span aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M12 21s6.5-5.2 6.5-10.2A6.5 6.5 0 0 0 5.5 10.8C5.5 15.8 12 21 12 21z"/><circle cx="12" cy="10.8" r="2.2"/></svg>
                        </span>
                        <?= h($property['area']) ?>, <?= h($property['city']) ?>
                    </p>
                    <h1><?= h($property['name']) ?></h1>
                    <?php if (trim((string) $property['summary']) !== ''): ?>
                        <p class="pg-pd-hero-lead"><?= h($property['summary']) ?></p>
                    <?php endif; ?>
                    <div class="pg-pd-hero-actions">
                        <button class="pg-pd-btn pg-pd-btn-gold visit-open" type="button" data-intent="visit" data-property="<?= h($property['id']) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                            Request Site Visit
                        </button>
                        <a class="pg-pd-btn pg-pd-btn-slate" href="#pg-pd-enquire">Enquire</a>
                    </div>

                    <?php if (count($heroSlides) > 1 || $videos !== []): ?>
                        <div class="pg-pd-hero-controls">
                            <?php if (count($heroSlides) > 1): ?>
                                <button type="button" class="pg-pd-hero-nav" data-pd-prev aria-label="Previous image">‹</button>
                                <span class="pg-pd-hero-count" data-pd-count>01 / <?= str_pad((string) count($heroSlides), 2, '0', STR_PAD_LEFT) ?></span>
                                <button type="button" class="pg-pd-hero-nav" data-pd-next aria-label="Next image">›</button>
                            <?php endif; ?>
                            <?php if ($videos !== []): ?>
                                <button type="button" class="pg-pd-hero-play" data-pd-play-video data-video="<?= h(webPath((string) $videos[0])) ?>" aria-label="Play property video">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M10 8.5v7l6-3.5z"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="pg-pd-spec-card" aria-label="Property specifications">
                    <?php if (($property['price_label'] ?? '') !== '' && $property['price_label'] !== 'Price on request'): ?>
                        <p class="pg-pd-spec-price-label">Starting From</p>
                        <p class="pg-pd-spec-price"><?= h($property['price_label']) ?>*</p>
                    <?php else: ?>
                        <p class="pg-pd-spec-price"><?= h($property['price_label'] !== '' ? $property['price_label'] : 'Price on request') ?></p>
                    <?php endif; ?>
                    <ul class="pg-pd-spec-list">
                        <?php if (($property['type'] ?? '') !== ''): ?>
                            <li>
                                <span class="pg-pd-spec-icon" data-icon="type" aria-hidden="true"></span>
                                <div><small>Type</small><strong><?= h($property['type']) ?></strong></div>
                            </li>
                        <?php endif; ?>
                        <?php if (($property['bhk'] ?? '') !== ''): ?>
                            <li>
                                <span class="pg-pd-spec-icon" data-icon="config" aria-hidden="true"></span>
                                <div><small>Configuration</small><strong><?= h($property['bhk']) ?></strong></div>
                            </li>
                        <?php endif; ?>
                        <?php if (($property['size'] ?? '') !== ''): ?>
                            <li>
                                <span class="pg-pd-spec-icon" data-icon="size" aria-hidden="true"></span>
                                <div><small>Size</small><strong><?= h($property['size']) ?></strong></div>
                            </li>
                        <?php endif; ?>
                        <?php if ($constructionStatus !== ''): ?>
                            <li>
                                <span class="pg-pd-spec-icon" data-icon="status" aria-hidden="true"></span>
                                <div><small>Status</small><strong><?= h($constructionStatus) ?></strong></div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </aside>
            </div>
        </section>

        <nav class="pg-pd-tabs" data-pd-tabs aria-label="Property sections">
            <div class="wrap pg-pd-tabs-inner">
                <?php foreach ($tabs as $key => $label): ?>
                    <?php
                    $showTab = true;
                    if ($key === 'plans' && !$hasPlans) {
                        $showTab = false;
                    }
                    if ($key === 'amenities' && !$hasAmenities) {
                        $showTab = false;
                    }
                    if ($key === 'media' && !$hasMedia) {
                        $showTab = false;
                    }
                    if ($key === 'builder' && !$hasBuilder) {
                        $showTab = false;
                    }
                    if ($key === 'status' && !$hasStatus) {
                        $showTab = false;
                    }
                    if ($key === 'brochure' && empty($property['brochure_path']) && !$unlocked) {
                        // Still show brochure tab — unlock flow lives there
                        $showTab = true;
                    }
                    if (!$showTab) {
                        continue;
                    }
                    ?>
                    <a class="pg-pd-tab<?= $key === 'overview' ? ' is-on' : '' ?>" href="#section-<?= h($key) ?>" data-pd-tab="<?= h($key) ?>"><?= h($label) ?></a>
                <?php endforeach; ?>
            </div>
        </nav>

        <div class="wrap pg-pd-layout">
            <div class="pg-pd-main">
                <section class="pg-pd-section" id="section-overview" data-pd-section="overview">
                    <div class="pg-pd-overview">
                        <div class="pg-pd-overview-copy">
                            <p class="pg-pd-kicker">Overview</p>
                            <h2>About <?= h($property['name']) ?></h2>
                            <p><?= nl2br(h((string) ($property['overview'] !== '' ? $property['overview'] : $property['summary']))) ?></p>
                            <?php if ($highlights !== []): ?>
                                <ul class="pg-pd-highlights">
                                    <?php foreach ($highlights as $item): ?>
                                        <li>
                                            <span class="pg-pd-hi-icon" data-icon="<?= h($amenityIcon((string) $item)) ?>" aria-hidden="true"></span>
                                            <span><?= h((string) $item) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <?php if ($overviewImage !== ''): ?>
                            <figure class="pg-pd-overview-media">
                                <img src="<?= h(webPath($overviewImage)) ?>" alt="<?= h($property['name']) ?> overview" loading="lazy">
                            </figure>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="pg-pd-section pg-pd-facts" id="section-facts" aria-label="Key details">
                    <dl class="pg-pd-facts-grid">
                        <?php
                        $facts = [
                            'State' => $property['state'],
                            'City' => $property['city'],
                            'Area' => $property['area'],
                            'Type' => $property['type'],
                            'Configuration' => $property['bhk'],
                            'Size' => $property['size'],
                            'Price' => $property['price_label'],
                            'Status' => $constructionStatus,
                        ];
                        foreach ($facts as $label => $value):
                            if (trim((string) $value) === '') {
                                continue;
                            }
                            ?>
                            <div>
                                <dt><?= h($label) ?></dt>
                                <dd><?= h((string) $value) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </section>

                <?php if ($hasAmenities): ?>
                    <section class="pg-pd-section" id="section-amenities" data-pd-section="amenities">
                        <div class="pg-pd-amenities">
                            <div class="pg-pd-amenities-intro">
                                <p class="pg-pd-kicker">Lifestyle</p>
                                <h2>World-Class Amenities</h2>
                                <?php if ($overviewImage !== ''): ?>
                                    <img src="<?= h(webPath($overviewImage)) ?>" alt="" loading="lazy">
                                <?php endif; ?>
                            </div>
                            <ul class="pg-pd-amenities-grid">
                                <?php foreach ($amenities as $amenityName): ?>
                                    <li>
                                        <span class="pg-pd-amenity-icon" data-icon="<?= h($amenityIcon((string) $amenityName)) ?>" aria-hidden="true"></span>
                                        <span><?= h((string) $amenityName) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasPlans): ?>
                    <section class="pg-pd-section" id="section-plans" data-pd-section="plans">
                        <p class="pg-pd-kicker">Layouts</p>
                        <div class="pg-pd-section-head">
                            <h2>Floor Plans</h2>
                            <?php if (($property['builtup_label'] ?? '') !== ''): ?>
                                <p class="pg-pd-muted">Built-up area <?= h((string) $property['builtup_label']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($unitTypes !== []): ?>
                            <div class="pg-pd-plans">
                                <?php foreach ($unitTypes as $unit): ?>
                                    <?php
                                    $planPath = trim((string) ($unit['floor_plan_path'] ?? ''));
                                    $isImage = $planPath !== '' && preg_match('/\.(jpe?g|png|webp|gif)$/i', $planPath) === 1;
                                    $configLabel = (string) ($unit['configuration'] ?? $property['bhk']);
                                    $areaLabel = formatAreaLabel(isset($unit['carpet_area_sqft']) ? (float) $unit['carpet_area_sqft'] : null);
                                    $priceLabel = !empty($unit['price']) ? formatPriceLabel((float) $unit['price']) : '';
                                    ?>
                                    <article class="pg-pd-plan-card<?= $isImage ? ' has-photo' : '' ?>">
                                        <?php if ($isImage): ?>
                                            <a class="pg-pd-plan-photo" href="<?= h(webPath($planPath)) ?>" target="_blank" rel="noopener">
                                                <img src="<?= h(webPath($planPath)) ?>" alt="<?= h($configLabel) ?> floor plan" loading="lazy">
                                            </a>
                                        <?php else: ?>
                                            <span class="pg-pd-plan-badge" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M4 19V9l8-5 8 5v10"/><path d="M9 19v-6h6v6"/></svg>
                                            </span>
                                        <?php endif; ?>
                                        <strong class="pg-pd-plan-config"><?= h($configLabel) ?></strong>
                                        <?php if ($areaLabel !== ''): ?>
                                            <span class="pg-pd-plan-area"><?= h($areaLabel) ?></span>
                                        <?php endif; ?>
                                        <?php if ($priceLabel !== ''): ?>
                                            <span class="pg-pd-plan-price"><?= h($priceLabel) ?></span>
                                        <?php endif; ?>
                                        <?php if ($planPath !== ''): ?>
                                            <a class="pg-pd-text-link" href="<?= h(webPath($planPath)) ?>" target="_blank" rel="noopener">View ↗</a>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <?php $roomCount = preg_match('/^(\d+) BHK$/', (string) $property['bhk'], $matches) === 1 ? (int) $matches[1] : 0; ?>
                            <article class="pg-pd-plan-card has-photo pg-pd-plan-illustrative">
                                <div class="pg-pd-plan-svg-wrap">
                                    <svg class="floor-plan" viewBox="0 0 840 480" role="img" aria-label="Representative <?= h($property['bhk']) ?> floor plan">
                                        <rect class="plan-outline" x="30" y="30" width="780" height="390" rx="3" />
                                        <rect class="plan-balcony" x="30" y="30" width="780" height="58" />
                                        <text x="420" y="65" text-anchor="middle">BALCONY / DECK</text>
                                        <rect class="plan-living" x="250" y="88" width="310" height="205" />
                                        <text x="405" y="178" text-anchor="middle">LIVING &amp; DINING</text>
                                        <rect class="plan-room" x="30" y="88" width="220" height="140" />
                                        <text x="140" y="154" text-anchor="middle">KITCHEN</text>
                                        <rect class="plan-room" x="560" y="88" width="250" height="205" />
                                        <text x="685" y="180" text-anchor="middle">MASTER BEDROOM</text>
                                        <rect class="plan-room" x="30" y="293" width="<?= $roomCount >= 3 ? '250' : '390' ?>" height="127" />
                                        <text x="<?= $roomCount >= 3 ? '155' : '225' ?>" y="362" text-anchor="middle">BEDROOM 2</text>
                                        <?php if ($roomCount >= 3): ?>
                                            <rect class="plan-room" x="280" y="293" width="250" height="127" />
                                            <text x="405" y="362" text-anchor="middle">BEDROOM 3</text>
                                        <?php endif; ?>
                                        <?php if ($roomCount >= 4): ?>
                                            <rect class="plan-room" x="530" y="293" width="280" height="127" />
                                            <text x="670" y="362" text-anchor="middle">BEDROOM 4</text>
                                        <?php else: ?>
                                            <rect class="plan-room" x="<?= $roomCount >= 3 ? '530' : '420' ?>" y="293" width="<?= $roomCount >= 3 ? '280' : '390' ?>" height="127" />
                                            <text x="<?= $roomCount >= 3 ? '670' : '615' ?>" y="362" text-anchor="middle">BATH / UTILITY</text>
                                        <?php endif; ?>
                                    </svg>
                                </div>
                                <div class="pg-pd-plan-body">
                                    <strong><?= h($property['bhk']) ?></strong>
                                    <div class="pg-pd-plan-stats">
                                        <span><?= h($property['size']) ?></span>
                                    </div>
                                    <p class="pg-pd-muted">Illustrative layout. Final dimensions are confirmed in official project documents.</p>
                                </div>
                            </article>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <section class="pg-pd-section" id="section-location" data-pd-section="location">
                    <p class="pg-pd-kicker">Location</p>
                    <h2>Where You’ll Be</h2>
                    <p><?= h($property['area']) ?>, <?= h($property['city']) ?>, <?= h($property['state']) ?>.</p>
                    <?php if (!empty($property['address'])): ?>
                        <p class="pg-pd-muted"><?= h((string) $property['address']) ?></p>
                    <?php endif; ?>
                    <?php if ($mapEmbed !== ''): ?>
                        <div class="pg-pd-map">
                            <iframe
                                title="Map for <?= h($property['name']) ?>"
                                src="<?= h($mapEmbed) ?>"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen
                            ></iframe>
                        </div>
                        <?php if (!empty($property['map_url'])): ?>
                            <p><a class="pg-pd-text-link" href="<?= h((string) $property['map_url']) ?>" target="_blank" rel="noopener">Open in Google Maps ↗</a></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="pg-pd-empty">Map coordinates will appear once a location is added in the CMS.</p>
                    <?php endif; ?>
                </section>

                <?php if ($hasMedia): ?>
                    <section class="pg-pd-section" id="section-media" data-pd-section="media">
                        <p class="pg-pd-kicker">Gallery</p>
                        <h2>Images / Videos</h2>
                        <div class="pg-pd-gallery" data-pd-gallery>
                            <?php foreach ($videos as $vi => $videoSrc): ?>
                                <figure class="pg-pd-gallery-item is-video">
                                    <video controls playsinline preload="metadata"<?= ($property['image'] ?? '') !== '' ? ' poster="' . h(webPath((string) $property['image'])) . '"' : '' ?>>
                                        <source src="<?= h(webPath((string) $videoSrc)) ?>" type="video/mp4">
                                    </video>
                                    <span class="pg-pd-badge">Video</span>
                                </figure>
                            <?php endforeach; ?>
                            <?php foreach ($gallery as $gi => $imageSrc): ?>
                                <button type="button" class="pg-pd-gallery-item" data-pd-lightbox="<?= h(webPath((string) $imageSrc)) ?>" data-pd-lightbox-index="<?= (int) $gi ?>">
                                    <img src="<?= h(webPath((string) $imageSrc)) ?>" alt="<?= h($property['name']) ?> gallery <?= $gi + 1 ?>" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasBuilder): ?>
                    <section class="pg-pd-section" id="section-builder" data-pd-section="builder">
                        <p class="pg-pd-kicker">Developer</p>
                        <h2>About the Builder</h2>
                        <?php foreach (preg_split("/\R{2,}/", $builderText) ?: [] as $chunk): ?>
                            <?php if (trim($chunk) !== ''): ?>
                                <p><?= nl2br(h(trim($chunk))) ?></p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>

                <?php if ($hasStatus): ?>
                    <section class="pg-pd-section" id="section-status" data-pd-section="status">
                        <p class="pg-pd-kicker">Progress</p>
                        <h2>Construction Status</h2>
                        <div class="pg-pd-status">
                            <?php if ($constructionStatus !== ''): ?>
                                <div><small>Current status</small><strong><?= h($constructionStatus) ?></strong></div>
                            <?php endif; ?>
                            <?php if (strcasecmp($constructionStatus, 'Pending') === 0 && !empty($property['launch_date'])): ?>
                                <div><small>Launch date</small><strong><?= h(date('j M Y', strtotime((string) $property['launch_date']))) ?></strong></div>
                            <?php endif; ?>
                            <?php if ($property['construction_progress'] !== null): ?>
                                <div>
                                    <small>Construction progress</small>
                                    <strong><?= (int) $property['construction_progress'] ?>%</strong>
                                    <div class="pg-pd-progress" aria-hidden="true"><span style="width: <?= max(0, min(100, (int) $property['construction_progress'])) ?>%"></span></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['possession_date'])): ?>
                                <div><small>Possession</small><strong><?= h((string) $property['possession_date']) ?></strong></div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="pg-pd-section" id="section-brochure" data-pd-section="brochure">
                    <div class="pg-pd-brochure">
                        <div class="pg-pd-brochure-cover" aria-hidden="true">
                            <span><?= h($property['name']) ?></span>
                            <small>Project Brochure</small>
                        </div>
                        <div class="pg-pd-brochure-copy">
                            <p class="pg-pd-kicker">Documents</p>
                            <h2>Project Brochure</h2>
                            <?php if ($unlocked): ?>
                                <p>Your enquiry unlocked this brochure. Download whenever you are ready.</p>
                            <?php else: ?>
                                <p>Share a few details to receive access to the official project brochure for <?= h($property['name']) ?>.</p>
                            <?php endif; ?>
                        </div>
                        <div class="pg-pd-brochure-actions">
                            <?php if ($unlocked): ?>
                                <a class="pg-pd-btn pg-pd-btn-outline" href="<?= h(webPath('actions/brochure.php?id=' . rawurlencode((string) $property['id']) . '&mode=download')) ?>">Download Brochure ↧</a>
                            <?php else: ?>
                                <button class="pg-pd-btn pg-pd-btn-outline visit-open" type="button" data-intent="brochure" data-property="<?= h($property['id']) ?>">View Brochure ↗</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="pg-pd-aside" id="pg-pd-enquire">
                <div class="pg-pd-enquire" data-pd-enquire>
                    <div class="pg-pd-enquire-head">
                        <span class="pg-pd-enquire-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4.5 10.8 12 4.6l7.5 6.2V19a1.6 1.6 0 0 1-1.6 1.6h-3.4v-5.4H9.5V20.6H6.1A1.6 1.6 0 0 1 4.5 19z"/></svg>
                        </span>
                        <div>
                            <p>Get in Touch</p>
                            <h2>Interested in <?= h($property['name']) ?>?</h2>
                        </div>
                    </div>
                    <?php
                    renderVisitFields('detail', 'enquire', (string) $property['id'], [
                        'form_class' => 'pg-pd-form',
                        'labels' => [
                            'name' => 'Full Name',
                            'phone' => 'Contact Number',
                            'email' => 'Email Address',
                            'message' => 'Message',
                        ],
                        'placeholders' => [
                            'name' => 'Enter your name',
                            'phone' => 'Enter number',
                            'email' => 'Enter your email',
                            'message' => 'Tell us your requirements...',
                        ],
                        'hide_property' => true,
                        'locked_property' => true,
                        'required_marks' => true,
                        'button' => 'Send Enquiry <span aria-hidden="true">↗</span>',
                    ]);
                    ?>
                    <?php if ($emiLoan > 0): ?>
                        <a class="pg-pd-emi-link" href="<?= h(webPath('pages/emi-calculator?loan=' . $emiLoan)) ?>">Estimate EMI for this property ↗</a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>

        <div class="pg-pd-sticky-mobile" data-pd-mobile-cta>
            <button class="pg-pd-btn pg-pd-btn-gold visit-open" type="button" data-intent="visit" data-property="<?= h($property['id']) ?>">Site Visit</button>
            <a class="pg-pd-btn pg-pd-btn-slate" href="#pg-pd-enquire">Enquire</a>
        </div>

        <div class="pg-pd-lightbox" data-pd-lightbox-modal hidden>
            <button type="button" class="pg-pd-lightbox-close" data-pd-lightbox-close aria-label="Close">×</button>
            <button type="button" class="pg-pd-lightbox-nav is-prev" data-pd-lightbox-prev aria-label="Previous">‹</button>
            <img src="" alt="" data-pd-lightbox-img>
            <button type="button" class="pg-pd-lightbox-nav is-next" data-pd-lightbox-next aria-label="Next">›</button>
        </div>

        <div class="pg-pd-video-modal" data-pd-video-modal hidden>
            <button type="button" class="pg-pd-lightbox-close" data-pd-video-close aria-label="Close video">×</button>
            <video controls playsinline data-pd-video-el></video>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
