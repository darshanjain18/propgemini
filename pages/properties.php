<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/properties.php';

$query = [
    'city' => (string) ($_GET['city'] ?? ''),
    'area' => (string) ($_GET['area'] ?? ''),
    'type' => (string) ($_GET['type'] ?? ''),
    'status' => (string) ($_GET['status'] ?? ''),
    'bhk' => (string) ($_GET['bhk'] ?? ''),
    'budget' => (string) ($_GET['budget'] ?? ''),
];
$results = filterProperties($query);
$activeFilters = array_filter($query, static function ($value, $key) {
    return $value !== '' && !($key === 'budget' && $value === '');
}, ARRAY_FILTER_USE_BOTH);
$featured = featuredProperty();

$pageTitle = 'Projects | PropGemini';
$pageDescription = 'Browse residences and workspaces in Pune, Mumbai and Gurgaon.';
$bodyClass = 'properties-page';
$pageStyles = ['public/css/featured.css'];
$pageScripts = ['public/js/featured.js'];

$featuredAmenityIcons = [
    'tree' => 'fa-solid fa-tree',
    'dumbbell' => 'fa-solid fa-dumbbell',
    'wifi' => 'fa-solid fa-wifi',
    'car' => 'fa-solid fa-car',
    'building' => 'fa-regular fa-building',
    'shield' => 'fa-solid fa-shield-halved',
    'water' => 'fa-solid fa-water-ladder',
    'leaf' => 'fa-solid fa-leaf',
];

$pickAmenityIcon = static function (string $label, int $index) use ($featuredAmenityIcons): string {
    $hay = strtolower($label);
    if (preg_match('/garden|landscape|courtyard|green|park|tree|lawn/', $hay)) {
        return $featuredAmenityIcons['tree'];
    }
    if (preg_match('/club|gym|fitness|amenit|sport|recreation/', $hay)) {
        return $featuredAmenityIcons['dumbbell'];
    }
    if (preg_match('/it|wifi|wi-fi|tech|corridor|connectiv|fiber|internet/', $hay)) {
        return $featuredAmenityIcons['wifi'];
    }
    if (preg_match('/road|metro|highway|airport|city|connect|transit|parking|car/', $hay)) {
        return $featuredAmenityIcons['car'];
    }
    if (preg_match('/pool|swim|water/', $hay)) {
        return $featuredAmenityIcons['water'];
    }
    if (preg_match('/secure|security|gated|rera/', $hay)) {
        return $featuredAmenityIcons['shield'];
    }
    $fallback = array_values($featuredAmenityIcons);
    return $fallback[$index % count($fallback)];
};

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <?php if ($featured !== null): ?>
        <?php
        $featuredBuilder = propertyBuilderLabel($featured);
        $featuredStatus = trim((string) ($featured['construction_status'] !== '' ? $featured['construction_status'] : $featured['status']));
        $featuredAmenities = array_values(array_filter(array_map('trim', $featured['amenities_list'] ?? []), static fn ($item) => $item !== ''));
        $featuredAmenities = array_slice($featuredAmenities, 0, 4);
        $hasBrochure = trim((string) ($featured['brochure_path'] ?? '')) !== '';
        $locationLabel = trim($featured['area'] . ($featured['city'] !== '' ? ' – ' . $featured['city'] : ''));

        // Prefer a strong architectural tower for the featured media panel.
        $featuredImage = 'public/images/projects/pune-tower-1.jpg';
        $towerCandidates = [
            'public/images/projects/pune-tower-1.jpg',
            'public/images/projects/mumbai-tower-1.jpg',
            'public/images/projects/gurgaon-tower-1.jpg',
            'public/images/projects/pune-tower-2.jpg',
            'public/images/projects/mumbai-tower-2.jpg',
        ];
        foreach ($towerCandidates as $candidate) {
            if (is_file(dirname(__DIR__) . '/' . $candidate)) {
                $featuredImage = $candidate;
                break;
            }
        }
        ?>
        <section class="pg-featured" aria-labelledby="pg-featured-title">
            <div class="pg-featured-shell">
                <div class="pg-featured-split">
                    <div class="pg-featured-copy" data-featured-reveal>
                        <svg class="pg-featured-waves" viewBox="0 0 420 220" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M10 150C70 100 120 40 180 55C240 70 260 130 320 120C370 112 395 70 410 50" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M20 175C85 130 130 75 185 88C245 102 265 155 325 148C375 142 395 100 410 85" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".55"/>
                        </svg>

                        <p class="pg-featured-kicker">Featured project</p>
                        <h1 id="pg-featured-title"><?= h($featured['name']) ?></h1>

                        <?php if ($featuredBuilder !== ''): ?>
                            <p class="pg-featured-builder">By <?= h($featuredBuilder) ?></p>
                        <?php endif; ?>

                        <?php if (trim((string) $featured['summary']) !== ''): ?>
                            <p class="pg-featured-summary"><?= h($featured['summary']) ?></p>
                        <?php endif; ?>

                        <ul class="pg-featured-facts">
                            <?php if ($locationLabel !== ''): ?>
                                <li>
                                    <span class="pg-featured-fact-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.6"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="pg-featured-fact-label">Location</span>
                                        <strong><?= h($locationLabel) ?></strong>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <?php if (trim((string) $featured['bhk']) !== ''): ?>
                                <li>
                                    <span class="pg-featured-fact-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 20V9.5L12 4l8 5.5V20" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M9 10.5h.01M15 10.5h.01M9 14h.01M15 14h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="pg-featured-fact-label">Configuration</span>
                                        <strong><?= h($featured['bhk']) ?></strong>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <?php if ($featuredStatus !== ''): ?>
                                <li>
                                    <span class="pg-featured-fact-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.6"/>
                                            <path d="M8.2 12.2 10.8 14.8 15.8 9.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="pg-featured-fact-label">Status</span>
                                        <strong><?= h($featuredStatus) ?></strong>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="pg-featured-actions">
                            <a class="pg-featured-btn pg-featured-btn-primary" href="<?= h(propertyUrl($featured['id'])) ?>">
                                View Project <span aria-hidden="true">↗</span>
                            </a>
                            <button
                                class="pg-featured-btn pg-featured-btn-outline visit-open"
                                type="button"
                                data-intent="brochure"
                                data-property="<?= h($featured['id']) ?>"
                            >
                                <span aria-hidden="true">↓</span> Download Brochure
                            </button>
                        </div>
                    </div>

                    <div class="pg-featured-media" data-featured-reveal data-featured-delay="1">
                        <div class="pg-featured-slash" aria-hidden="true"></div>
                        <figure class="pg-featured-frame">
                            <img
                                src="<?= h(webPath($featuredImage)) ?>"
                                alt="<?= h($featured['name']) ?>"
                                width="1400"
                                height="980"
                                loading="eager"
                                decoding="async"
                            >
                        </figure>
                        <?php if (trim((string) $featured['price_label']) !== ''): ?>
                            <div class="pg-featured-price-pill"><?= h($featured['price_label']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($featuredAmenities !== []): ?>
                    <ul class="pg-featured-strip" aria-label="Project highlights">
                        <?php foreach ($featuredAmenities as $index => $amenity): ?>
                            <li data-featured-reveal data-featured-delay="<?= (int) $index ?>">
                                <span class="pg-featured-strip-icon" aria-hidden="true">
                                    <i class="<?= h($pickAmenityIcon($amenity, $index)) ?>"></i>
                                </span>
                                <span><?= h($amenity) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="pg-featured pg-featured-empty" aria-label="Featured project">
            <div class="pg-featured-empty-card">
                <p class="pg-featured-kicker">Featured project</p>
                <h2>No featured project selected</h2>
                <p>Mark a published property as featured in the admin panel to showcase it here.</p>
            </div>
        </section>
    <?php endif; ?>

    <section class="wrap filter-block">
        <?php renderPropertyFilter($query); ?>
    </section>

    <section class="section wrap">
        <div class="section-head properties-results-head">
            <div>
                <div class="eyebrow">Projects</div>
                <h2>
                    <?= count($results) ?>
                    <?= count($results) === 1 ? 'project' : 'projects' ?>
                    found
                </h2>
            </div>
        </div>

        <?php if ($activeFilters !== []): ?>
            <div class="active-filter-list">
                <?php foreach ($activeFilters as $key => $value): ?>
                    <span class="active-filter">
                        <?= h(ucfirst($key)) ?>:
                        <strong>
                            <?= h($key === 'budget' ? budgetRanges()[$value] ?? $value : $value) ?>
                        </strong>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($results === []): ?>
            <p class="empty-note">No published projects match those filters yet. Clear a field and try again.</p>
        <?php else: ?>
            <div class="property-grid">
                <?php foreach ($results as $property): ?>
                    <?php renderPropertyCard($property); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
