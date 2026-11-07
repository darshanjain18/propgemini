<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/properties.php';
require_once dirname(__DIR__) . '/includes/analytics.php';

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
$unlocked = $property && !empty($_SESSION['brochure_ok'][$property['id']]);
$mapEmbed = $property ? propertyMapEmbedUrl($property) : '';
$emiLoan = $property && ($property['price'] ?? 0) > 0 ? (int) $property['price'] * 100000 : 0;

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <?php if ($property === null): ?>
        <section class="section wrap">
            <h1>This property could not be found.</h1>
            <a class="text-link" href="<?= h(webPath('pages/properties')) ?>">Back to projects ↗</a>
        </section>
    <?php else: ?>
        <section class="detail-cinema">
            <img src="<?= h(webPath($property['image'])) ?>" alt="<?= h($property['name']) ?>">
            <div class="detail-cinema-copy wrap">
                <div class="eyebrow"><?= h($property['area']) ?> · <?= h($property['city']) ?></div>
                <h1><?= h($property['name']) ?></h1>
                <p><?= h($property['summary']) ?></p>
                <div class="detail-actions">
                    <button class="btn light visit-open" type="button" data-intent="visit" data-property="<?= h($property['id']) ?>">Request Site Visit</button>
                    <a class="btn outline" href="<?= h(webPath('pages/enquire.php?property=' . rawurlencode((string) $property['id']))) ?>">Enquire</a>
                </div>
            </div>
        </section>
        <section class="section wrap detail-facts-band">
            <dl class="detail-facts">
                <div><dt>State</dt><dd><?= h($property['state']) ?></dd></div>
                <div><dt>Type</dt><dd><?= h($property['type']) ?></dd></div>
                <div><dt>Configuration</dt><dd><?= h($property['bhk']) ?></dd></div>
                <div><dt>Size</dt><dd><?= h($property['size']) ?></dd></div>
                <div><dt>Status</dt><dd><?= h($property['status']) ?></dd></div>
                <div><dt>Price</dt><dd><?= h($property['price_label']) ?></dd></div>
            </dl>
        </section>

        <section class="wrap detail-switcher">
            <div class="detail-nav" role="tablist">
                <?php foreach (['overview' => 'Overview', 'plans' => 'Floor plans', 'amenities' => 'Amenities', 'location' => 'Location', 'media' => 'Images/Videos', 'builder' => 'About builder', 'status' => 'Status', 'brochure' => 'Brochure'] as $key => $label): ?>
                    <button type="button" class="detail-tab<?= $key === 'overview' ? ' is-on' : '' ?>" data-panel="<?= h($key) ?>"><?= h($label) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="detail-panel is-on" id="panel-overview">
                <h2>Overview</h2>
                <p><?= h((string) ($property['overview'] !== '' ? $property['overview'] : $property['summary'])) ?></p>
                <?php if (!empty($property['address'])): ?>
                    <p class="muted"><?= h((string) $property['address']) ?></p>
                <?php endif; ?>
            </div>
            <div class="detail-panel" id="panel-plans" hidden>
                <div class="floor-plan-meta">
                    <h2>Floor plans</h2>
                    <?php if (($property['builtup_label'] ?? '') !== ''): ?>
                        <p class="builtup-area"><span>Built-up area</span> <?= h((string) $property['builtup_label']) ?></p>
                    <?php endif; ?>
                </div>
                <?php
                $unitTypes = $property['unit_types'] ?? [];
                $roomCount = preg_match('/^(\d+) BHK$/', $property['bhk'], $matches) === 1 ? (int) $matches[1] : 0;
                ?>
                <?php if ($unitTypes !== []): ?>
                    <div class="unit-type-list">
                        <?php foreach ($unitTypes as $unit): ?>
                            <?php $planPath = trim((string) ($unit['floor_plan_path'] ?? '')); ?>
                            <article class="unit-type-card">
                                <strong><?= h((string) ($unit['configuration'] ?? $property['bhk'])) ?></strong>
                                <span><?= h(formatAreaLabel(isset($unit['carpet_area_sqft']) ? (float) $unit['carpet_area_sqft'] : null)) ?></span>
                                <?php if (!empty($unit['price'])): ?>
                                    <span><?= h(formatPriceLabel((float) $unit['price'])) ?></span>
                                <?php endif; ?>
                                <?php if ($planPath !== '' && preg_match('/\.(jpe?g|png|webp|gif)$/i', $planPath) === 1): ?>
                                    <a class="floor-plan-photo" href="<?= h(webPath($planPath)) ?>" target="_blank" rel="noopener">
                                        <img src="<?= h(webPath($planPath)) ?>" alt="<?= h((string) ($unit['configuration'] ?? 'Floor plan')) ?> floor plan">
                                    </a>
                                <?php elseif ($planPath !== ''): ?>
                                    <a class="text-link" href="<?= h(webPath($planPath)) ?>" target="_blank" rel="noopener">View floor plan ↗</a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($unitTypes === [] && $roomCount > 0): ?>
                    <p>A representative <?= h($property['bhk']) ?> blueprint for the <?= h($property['size']) ?> configuration. Final layouts and dimensions are confirmed in the official project documents.</p>
                    <div class="floor-plan-card">
                        <div class="floor-plan-heading">
                            <span class="eyebrow"><?= h($property['bhk']) ?> configuration</span>
                            <strong><?= h($property['size']) ?></strong>
                        </div>
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
                        <p class="floor-plan-note">Illustrative layout · <?= h($property['bhk']) ?> · <?= h($property['size']) ?></p>
                    </div>
                <?php elseif ($unitTypes === []): ?>
                    <p class="empty-note">Floor plans will appear here once they are added in the admin panel.</p>
                <?php endif; ?>
            </div>
            <div class="detail-panel" id="panel-amenities" hidden>
                <h2>Amenities</h2>
                <ul class="amenity-grid">
                    <?php
                    $amenities = $property['amenities_list'] !== []
                        ? $property['amenities_list']
                        : ['Clubhouse', 'Gardens', 'Fitness'];
                    foreach ($amenities as $index => $amenityName):
                    ?>
                        <li class="amenity">
                            <span><?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                            <h3><?= h((string) $amenityName) ?></h3>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="detail-panel" id="panel-location" hidden>
                <h2>Location</h2>
                <p><?= h($property['area']) ?>, <?= h($property['city']) ?>, <?= h($property['state']) ?>.</p>
                <?php if (!empty($property['address'])): ?>
                    <p><?= h((string) $property['address']) ?></p>
                <?php endif; ?>
                <?php if ($mapEmbed !== ''): ?>
                    <div class="property-map">
                        <iframe
                            title="Map for <?= h($property['name']) ?>"
                            src="<?= h($mapEmbed) ?>"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>
                    <?php if (!empty($property['map_url'])): ?>
                        <p><a class="text-link" href="<?= h((string) $property['map_url']) ?>" target="_blank" rel="noopener">Open in Google Maps ↗</a></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="empty-note">Map coordinates will appear once a location is added in the CMS.</p>
                <?php endif; ?>
            </div>
            <div class="detail-panel" id="panel-media" hidden>
                <div class="media-section-head">
                    <div>
                        <span class="eyebrow">Property gallery</span>
                        <h2>Images / Videos</h2>
                    </div>
                    <p><?= h($property['name']) ?> · <?= h($property['area']) ?>, <?= h($property['city']) ?></p>
                </div>
                <?php
                $mediaTiles = [];
                $videos = $property['videos'] !== [] ? $property['videos'] : (($property['video'] ?? '') !== '' ? [$property['video']] : []);
                $images = $property['gallery'] !== [] ? $property['gallery'] : (($property['image'] ?? '') !== '' ? [$property['image']] : []);

                if ($videos !== []) {
                    $mediaTiles[] = ['type' => 'video', 'src' => (string) $videos[0]];
                    $videos = array_slice($videos, 1);
                } elseif ($images !== []) {
                    $mediaTiles[] = ['type' => 'image', 'src' => (string) $images[0]];
                    $images = array_slice($images, 1);
                }

                foreach ($videos as $videoSrc) {
                    $mediaTiles[] = ['type' => 'video', 'src' => (string) $videoSrc];
                }
                foreach ($images as $imageSrc) {
                    $mediaTiles[] = ['type' => 'image', 'src' => (string) $imageSrc];
                }
                $poster = (string) ($property['image'] ?? '');
                ?>
                <?php if ($mediaTiles === []): ?>
                    <p class="empty-note">Media will appear here once images or a video are added.</p>
                <?php else: ?>
                    <div class="masonry-gallery" aria-label="Property media gallery">
                        <?php foreach ($mediaTiles as $index => $tile): ?>
                            <figure class="masonry-tile masonry-tile-<?= $index + 1 ?> masonry-<?= h($tile['type']) ?>">
                                <?php if ($tile['type'] === 'video'): ?>
                                    <video class="masonry-media" controls playsinline preload="metadata"<?= $poster !== '' ? ' poster="' . h(webPath($poster)) . '"' : '' ?>>
                                        <source src="<?= h(webPath($tile['src'])) ?>" type="video/mp4">
                                    </video>
                                    <span class="media-badge">Video</span>
                                <?php else: ?>
                                    <img class="masonry-media" loading="lazy" src="<?= h(webPath($tile['src'])) ?>" alt="<?= h($property['name']) ?> media <?= $index + 1 ?>">
                                    <span class="media-badge">Image</span>
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="detail-panel" id="panel-builder" hidden>
                <h2>About the builder</h2>
                <?php if (trim((string) ($property['about_builder'] ?? '')) !== ''): ?>
                    <?php foreach (preg_split("/\R{2,}/", trim((string) $property['about_builder'])) ?: [] as $chunk): ?>
                        <?php if (trim($chunk) !== ''): ?>
                            <p><?= nl2br(h(trim($chunk))) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-note">A note about the builder will appear here once it is added.</p>
                <?php endif; ?>
            </div>
            <div class="detail-panel" id="panel-status" hidden>
                <h2>Status</h2>
                <p>Current status: <?= h((string) ($property['construction_status'] !== '' ? $property['construction_status'] : $property['status'])) ?>.</p>
                <?php if (strcasecmp((string) $property['construction_status'], 'Pending') === 0 && !empty($property['launch_date'])): ?>
                    <p>Launch date: <?= h(date('j M Y', strtotime((string) $property['launch_date']))) ?></p>
                <?php endif; ?>
                <?php if ($property['construction_progress'] !== null): ?>
                    <p>Construction progress: <?= (int) $property['construction_progress'] ?>%</p>
                <?php endif; ?>
                <?php if (!empty($property['possession_date'])): ?>
                    <p>Possession: <?= h((string) $property['possession_date']) ?></p>
                <?php endif; ?>
            </div>
            <div class="detail-panel" id="panel-brochure" hidden>
                <span class="eyebrow">Project information</span>
                <h2>Brochure</h2>
                <?php if ($unlocked): ?>
                    <p>Your enquiry unlocked this brochure. Download whenever you are ready.</p>
                    <div class="brochure-actions">
                        <a class="btn" href="<?= h(webPath('actions/brochure.php?id=' . rawurlencode((string) $property['id']) . '&mode=download')) ?>">Download brochure ↧</a>
                    </div>
                <?php else: ?>
                    <p>Send a quick enquiry to receive access to this property's brochure. Your selected property is included automatically.</p>
                    <div class="brochure-actions">
                        <button class="btn visit-open" type="button" data-intent="brochure" data-property="<?= h($property['id']) ?>">View brochure ↗</button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <div class="detail-sticky">
            <button class="btn visit-open" type="button" data-intent="visit" data-property="<?= h($property['id']) ?>">Request Site Visit</button>
            <?php if ($emiLoan > 0): ?>
                <a class="btn outline" href="<?= h(webPath('pages/emi-calculator?loan=' . $emiLoan)) ?>">Estimate EMI</a>
            <?php endif; ?>
            <a class="btn light" href="<?= h(webPath('pages/enquire.php?property=' . rawurlencode((string) $property['id']))) ?>">Enquire</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
