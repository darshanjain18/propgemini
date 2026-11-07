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

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <?php if ($featured !== null): ?>
        <?php $featuredBuilder = propertyBuilderLabel($featured); ?>
        <section class="wrap featured-project">
            <a class="featured-layout" href="<?= h(propertyUrl($featured['id'])) ?>">
                <div class="featured-media">
                    <img src="<?= h(webPath($featured['image'])) ?>" alt="<?= h($featured['name']) ?>" loading="eager">
                </div>
                <div class="featured-copy">
                    <div class="eyebrow">Featured project</div>
                    <h2><?= h($featured['name']) ?></h2>
                    <?php if ($featuredBuilder !== ''): ?>
                        <div class="featured-builder">By <?= h($featuredBuilder) ?></div>
                    <?php endif; ?>
                    <p><?= h($featured['summary']) ?></p>
                    <div class="featured-meta">
                        <span><?= h($featured['area']) ?> · <?= h($featured['city']) ?></span>
                        <span><?= h($featured['bhk']) ?></span>
                        <span><?= h($featured['status']) ?></span>
                    </div>
                    <div class="featured-price"><?= h($featured['price_label']) ?></div>
                    <span class="featured-cta">View project <span>↗</span></span>
                </div>
            </a>
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
