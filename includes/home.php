<?php
declare(strict_types=1);

require_once __DIR__ . '/properties.php';

$propertiesUrl = webPath('pages/properties.php');
?>
<main>
<section class="hero" id="home" aria-label="PropGemini homepage hero">
    <img class="hero-img reveal-media" src="<?= h(webPath('public/images/hero.jpg')) ?>" alt="Modern residential tower at dusk" width="1920" height="1080" decoding="async">
    <div class="hero-arcs" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="wrap hero-content">
        <p class="eyebrow reveal-item" data-reveal-delay="0">Property, reimagined</p>
        <h1 class="reveal-item" data-reveal-delay="1">Exceptional homes.<br><em>Extraordinary living.</em></h1>
        <p class="reveal-item" data-reveal-delay="2">Explore distinctive residences and sought-after addresses across Pune, Mumbai and Gurgaon. Find a space that feels right for your lifestyle and ambitions.</p>
        <div class="hero-actions reveal-item" data-reveal-delay="3">
            <a class="btn light" href="<?= h($propertiesUrl) ?>">Explore properties <span>↗</span></a>
            <button class="btn outline visit-open" type="button" data-intent="visit">Request Site Visit</button>
        </div>
    </div>
</section>
<div class="wrap search-panel reveal-block">
    <?php renderPropertyFilter(); ?>
    <div class="home-marquee" aria-hidden="true">
        <div class="home-marquee-track">
            <span>Pune</span><i></i><span>Mumbai</span><i></i><span>Gurgaon</span><i></i><span>Luxury Living</span><i></i><span>Premium Residences</span><i></i><span>Your Dream Homes</span><i></i><span>Prime Locations</span><i></i>
            <span>Pune</span><i></i><span>Mumbai</span><i></i><span>Gurgaon</span><i></i><span>Luxury Living</span><i></i><span>Premium Residences</span><i></i><span>Transparent Pricing</span><i></i><span>Your Dream Homes</span><i></i><span>Prime Locations</span><i></i>
        </div>
    </div>
</div>
<section class="section wrap" id="properties">
    <div class="section-head reveal-block">
        <div>
            <div class="eyebrow">Best choice</div>
            <h2>Exceptional spaces.<br>Thoughtful choices.</h2>
        </div>
        <div class="property-tools">
            <div class="property-tools-nav">
                <button class="rail-btn" type="button" id="propertyPrev" aria-label="Scroll properties left">←</button>
                <button class="rail-btn" type="button" id="propertyNext" aria-label="Scroll properties right">→</button>
            </div>
            <a class="text-link" href="<?= h($propertiesUrl) ?>">View all projects ↗</a>
        </div>
    </div>
    <div class="properties" id="propertyGrid">
        <?php
        $homeRail = featuredProperties(6);
        foreach ($homeRail as $index => $property):
            renderPropertyCard($property, $index);
        endforeach;
        ?>
    </div>
</section>
<section class="section city-section" id="cities">
    <div class="wrap">
        <div class="section-head reveal-block">
            <div>
                <h2>Find your part<br>of the city.</h2>
            </div>
        </div>
        <div class="city-row">
            <?php foreach (['Pune' => ['Space to grow', 'Discover thoughtfully designed residences, vibrant neighbourhoods and a lifestyle that grows with you.', 'public/images/insight-pune.jpg'], 'Mumbai' => ['The city, your way',"Explore prestigious addresses, iconic skylines and refined living in India's city of dreams.", 'public/images/insight-mumbai.jpg'], 'Gurgaon' => ['Close to work','Discover premium residences, thriving business hubs and a lifestyle shaped by modern aspirations.', 'public/images/insight-gurgaon.jpg']] as $city => [$title, $copy, $image]): ?>
                <a class="city-panel reveal-item" href="<?= h($propertiesUrl . '?city=' . rawurlencode($city)) ?>">
                    <img src="<?= h(webPath($image)) ?>" alt="">
                    <span class="cn"><?= h($city) ?></span>
                    <h3><?= h($title) ?></h3>
                    <p><?= h($copy) ?></p>
                    <span class="go">Explore <?= h($city) ?> ↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="pg-home-amenities" id="amenities" aria-labelledby="pg-home-amenities-heading">
    <div class="wrap pg-home-amenities-inner">
        <header class="pg-home-amenities-head reveal-block">
            <p class="pg-home-amenities-kicker">Living</p>
            <div class="pg-home-amenities-title-row">
                <h2 id="pg-home-amenities-heading">Amenities that elevate <em>everyday life</em></h2>
                <!-- <span class="pg-home-amenities-divider" aria-hidden="true"></span> -->
                <!-- <p class="pg-home-amenities-lead">Thoughtfully curated spaces for wellness, leisure, family and peace of mind.</p> -->
            </div>
        </header>

        <div class="pg-home-amenities-grid">
            <?php
            $homeAmenities = [
                [
                    'title' => 'Clubhouse',
                    'copy' => 'Lounges, indoor games and space for gatherings.',
                    'image' => 'public/images/clubhouse.jpg',
                    'icon' => 'club',
                ],
                [
                    'title' => 'Landscaped gardens',
                    'copy' => 'Green courtyards and walking paths for everyday calm.',
                    'image' => 'public/images/landscaped-gardens.jpg',
                    'icon' => 'garden',
                ],
                [
                    'title' => 'Fitness & wellness',
                    'copy' => 'Gym and open-air spaces for yoga and training.',
                    'image' => 'public/images/fitness.jpg',
                    'icon' => 'fitness',
                ],
                [
                    'title' => "Children's play",
                    'copy' => 'Safe play areas designed for different age groups.',
                    'image' => 'public/images/childrens-play.jpg',
                    'icon' => 'kids',
                ],
                [
                    'title' => 'Security',
                    'copy' => 'Controlled access and monitored entry points.',
                    'image' => 'public/images/security.jpg',
                    'icon' => 'security',
                ],
                [
                    'title' => 'Parking & power',
                    'copy' => 'Visitor parking and power backup for everyday ease.',
                    'image' => 'public/images/parking.jpg',
                    'icon' => 'parking',
                ],
            ];
            foreach ($homeAmenities as $index => $amenity):
            ?>
                <article class="pg-home-amenity-card reveal-item" data-reveal-delay="<?= (int) ($index % 3) ?>">
                    <div class="pg-home-amenity-shell">
                        <span class="pg-home-amenity-frame" aria-hidden="true"></span>
                        <div class="pg-home-amenity-face">
                            <img class="pg-home-amenity-media" src="<?= h(webPath($amenity['image'])) ?>" alt="<?= h($amenity['title']) ?>" loading="lazy" decoding="async" width="720" height="560">
                            <span class="pg-home-amenity-veil" aria-hidden="true"></span>
                            <div class="pg-home-amenity-copy">
                                <h3><?= h($amenity['title']) ?></h3>
                                <p><?= h($amenity['copy']) ?></p>
                            </div>
                        </div>
                    </div>
                    <span class="pg-home-amenity-icon" data-icon="<?= h($amenity['icon']) ?>" aria-hidden="true">
                        <?php if ($amenity['icon'] === 'club'): ?>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M5 18.5V10.2c0-.6.3-1.2.8-1.5L12 5.5l6.2 3.2c.5.3.8.9.8 1.5v8.3"/><path d="M9 18.5v-4.2h6v4.2"/><path d="M4 18.5h16"/></svg>
                        <?php elseif ($amenity['icon'] === 'garden'): ?>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 20V11"/><path d="M12 14c-3.2 0-5.5-2-5.5-4.8C6.5 6.4 9 5 12 5c0 3 1.8 5.5 4.8 5.5-1.2 2.4-2.9 3.5-4.8 3.5Z"/></svg>
                        <?php elseif ($amenity['icon'] === 'fitness'): ?>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M3.5 10.5v3"/><path d="M20.5 10.5v3"/><path d="M6 9.2v5.6"/><path d="M18 9.2v5.6"/><path d="M6 12h12"/><path d="M8.2 8.5v7"/><path d="M15.8 8.5v7"/></svg>
                        <?php elseif ($amenity['icon'] === 'kids'): ?>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M7 19V9.5"/><path d="M7 9.5c2.4-2.8 5-4 8.5-4"/><path d="M12.5 19v-7.2"/><path d="M12.5 11.8 16 19"/><circle cx="7" cy="6.2" r="1.3"/></svg>
                        <?php elseif ($amenity['icon'] === 'security'): ?>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 3.8 5.5 6.5v5.2c0 4 2.7 7.4 6.5 8.5 3.8-1.1 6.5-4.5 6.5-8.5V6.5L12 3.8Z"/><path d="m9.6 12 1.7 1.7 3.2-3.4"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M4 15.5h16"/><path d="M6.2 15.5V11l5.8-3.5L17.8 11v4.5"/><path d="M8.2 18.2h7.6"/><circle cx="8.4" cy="15.5" r="1.4"/><circle cx="15.6" cy="15.5" r="1.4"/></svg>
                        <?php endif; ?>
                    </span>
                    <span class="pg-home-amenity-go" aria-hidden="true">→</span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="pg-home-why" id="about" aria-labelledby="pg-home-why-heading">
    <div class="wrap pg-home-why-inner">
        <div class="pg-home-why-media reveal-media">
            <div class="pg-home-why-frame">
                <span class="pg-home-why-outline" aria-hidden="true"></span>
                <div class="pg-home-why-photo">
                    <img loading="lazy" src="<?= h(webPath('public/images/property-spotlight-luxury-interior.jpg')) ?>" alt="Luxury living room interior" width="900" height="1100" decoding="async">
                </div>
                <p class="pg-home-why-caption">A smarter way to buy luxury real estate.</p>
            </div>
        </div>

        <div class="pg-home-why-content">
            <p class="pg-home-why-kicker reveal-item">Why choose PropGemini</p>
            <h2 id="pg-home-why-heading" class="reveal-item">Your Trusted Partner in <em>Luxury Real Estate.</em></h2>

            <div class="pg-home-why-cards">
                <article class="pg-home-why-card reveal-item" data-reveal-delay="0">
                    <span class="pg-home-why-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="10.2" r="6.4"/><path d="m9.2 16.2 2.8-1.4 2.8 1.4v4.2l-2.8-1.5-2.8 1.5z"/><path d="m10.1 10.2 1.3 1.3 2.6-2.7"/></svg>
                    </span>
                    <div>
                        <h3>RERA Certified Projects</h3>
                        <p>Invest with confidence in RERA-registered developments.</p>
                    </div>
                </article>

                <article class="pg-home-why-card reveal-item" data-reveal-delay="1">
                    <span class="pg-home-why-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 20.5s-6.2-5.1-6.2-9.4a6.2 6.2 0 1 1 12.4 0c0 4.3-6.2 9.4-6.2 9.4Z"/><circle cx="12" cy="11.1" r="2.2"/></svg>
                    </span>
                    <div>
                        <h3>Premium Locations</h3>
                        <p>Handpicked properties in Mumbai, Pune and Gurgaon's prime areas.</p>
                    </div>
                </article>

                <article class="pg-home-why-card reveal-item" data-reveal-delay="0">
                    <span class="pg-home-why-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><rect x="4.5" y="11.5" width="15" height="7.5" rx="1.2"/><rect x="6.5" y="7.2" width="11" height="4.3" rx="1"/><rect x="8.5" y="3.8" width="7" height="3.4" rx=".9"/></svg>
                    </span>
                    <div>
                        <h3>Curated Properties</h3>
                        <p>A considered selection of residences and commercial spaces chosen for their quality, appeal and potential.</p>
                    </div>
                </article>

                <article class="pg-home-why-card reveal-item" data-reveal-delay="1">
                    <span class="pg-home-why-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4.8 14.2v-1.4a7.2 7.2 0 0 1 14.4 0v1.4"/><path d="M4.8 14.2h2.3v4.2H4.8z"/><path d="M16.9 14.2h2.3v4.2h-2.3z"/><path d="M12 19.2h2.4a2.1 2.1 0 0 0 2.1-2.1v-.8"/></svg>
                    </span>
                    <div>
                        <h3>Personalised Guidance</h3>
                        <p>From your first enquiry to site visits and beyond, receive support at every step of your property journey.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
<section class="section wrap spotlight-section">
    <div class="spotlight reveal-block">
        <div class="spotlight-media">
            <div class="spotlight-img">
                <img loading="lazy" src="<?= h(webPath('public/images/property-spotlight-luxury-interior.jpg')) ?>" alt="Featured sample property interior">
            </div>
            <div class="spotlight-frame" aria-hidden="true"></div>
            <span class="spotlight-chip">Featured residence</span>
        </div>
        <div class="spotlight-copy">
            <div class="eyebrow">Property spotlight</div>
            <h2>An Address That<br><em> Reflects You.</em></h2>
            <p>Step into a residence where contemporary living, considered design and an exceptional neighbourhood create a life of distinction.</p>
            <div class="spotlight-data">
                <div class="spotlight-stat"><span>Location</span><strong>Pune, Maharashtra</strong></div>
                <div class="spotlight-stat"><span>Configuration</span><strong>3 BHK</strong></div>
                <div class="spotlight-stat"><span>Built-up</span><strong>1,450 sq. ft.</strong></div>
            </div>
            <div class="spotlight-actions">
                <a class="btn" href="<?= h(propertyUrl('canopy')) ?>">View this property <span>↗</span></a>
                <a class="text-link" href="<?= h($propertiesUrl) ?>">Browse all homes ↗</a>
            </div>
        </div>
    </div>
</section>
<?php
$pathSteps = [
    [
        'label' => 'Define Your Vision',
        'title' => 'Define Your Vision',
        'copy' => 'Tell us what home means to you—your preferred location, lifestyle, budget and aspirations.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5.5 6.8h9.2A2.8 2.8 0 0 1 17.5 9.6v4.2a2.8 2.8 0 0 1-2.8 2.8H11l-3.4 2.6v-2.6H8.3A2.8 2.8 0 0 1 5.5 13.8V6.8Z"/><path d="M9 10.2h5.2M9 12.6h3.4"/></svg>',
    ],
    [
        'label' => 'Discover the Possibilities',
        'title' => 'Discover the Possibilities',
        'copy' => 'Discover properties aligned with your priorities.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="5.2"/><path d="m15.2 15.2 3.3 3.3"/></svg>',
    ],
    [
        'label' => 'Find Your Perfect Match',
        'title' => 'Find Your Perfect Match',
        'copy' => 'Compare your favourites, discover the finer details and narrow down your choices with confidence.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 4.5h8a1.2 1.2 0 0 1 1.2 1.2v13.2L12 16.2l-5.2 2.7V5.7A1.2 1.2 0 0 1 8 4.5Z"/></svg>',
    ],
    [
        'label' => 'Experience It Firsthand',
        'title' => 'Site visit',
        'copy' => 'Arrange a site visit, explore the spaces and take the next step towards your new address.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4.5" y="6" width="15" height="13.5" rx="2"/><path d="M8 4.5v3M16 4.5v3M4.5 10.5h15"/></svg>',
    ],
];
?>
<section class="pg-home-path" id="pg-home-path" aria-labelledby="pg-home-path-heading" data-pg-path>
    <div class="pg-home-path-arch" aria-hidden="true">
        <svg viewBox="0 0 460 340" fill="none" focusable="false">
            <path d="M52 292V152l178-108 178 108v140" stroke="currentColor" stroke-width="1.15"/>
            <path d="M104 292V178h118v114M246 292V164h118v128" stroke="currentColor" stroke-width="1.15"/>
            <path d="M138 210h50M138 236h50M138 262h50M278 196h50M278 222h50M278 248h50" stroke="currentColor" stroke-width="1.05"/>
            <path d="M230 44v34M214 78h32" stroke="currentColor" stroke-width="1.05"/>
            <path d="M188 112h84v28h-84z" stroke="currentColor" stroke-width="1.05"/>
            <path d="M52 292h356" stroke="currentColor" stroke-width="1.15"/>
        </svg>
    </div>
    <div class="wrap pg-home-path-inner">
        <header class="pg-home-path-head reveal-block">
            <p class="pg-home-path-kicker">How it works</p>
            <h2 id="pg-home-path-heading">A simple process,<br>for your <em>perfect home.</em></h2>
        </header>

        <ol class="pg-home-path-track" role="list">
            <?php foreach ($pathSteps as $index => $step): ?>
                <li
                    class="pg-home-path-step reveal-item<?= $index === 0 ? ' is-active' : '' ?>"
                    data-reveal-delay="<?= (int) $index ?>"
                    data-step="<?= (int) $index ?>"
                    data-title="<?= h($step['title']) ?>"
                    data-copy="<?= h($step['copy']) ?>"
                >
                    <button
                        type="button"
                        class="pg-home-path-trigger"
                        aria-label="Step <?= (int) ($index + 1) ?>: <?= h($step['label']) ?>"
                        aria-controls="pg-home-path-panel"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                        data-path-step="<?= (int) $index ?>"
                    >
                        <span class="pg-home-path-badge" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></span>
                        <span class="pg-home-path-point">
                            <span class="pg-home-path-stem" aria-hidden="true"></span>
                            <span class="pg-home-path-icon"><?= $step['icon'] ?></span>
                            <span class="pg-home-path-label"><?= h($step['label']) ?></span>
                        </span>
                    </button>
                    <article class="pg-home-path-card">
                        <span class="pg-home-path-icon" aria-hidden="true"><?= $step['icon'] ?></span>
                        <h3><?= h($step['title']) ?></h3>
                        <p><?= h($step['copy']) ?></p>
                    </article>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="pg-home-path-stage">
            <article class="pg-home-path-panel" id="pg-home-path-panel" aria-live="polite">
                <div class="pg-home-path-panel-media" aria-hidden="true">
                    <span class="pg-home-path-panel-icon" data-path-panel-icon></span>
                </div>
                <span class="pg-home-path-panel-rule" aria-hidden="true"></span>
                <div class="pg-home-path-panel-body">
                    <h3 data-path-panel-title><?= h($pathSteps[0]['title']) ?></h3>
                    <p data-path-panel-copy><?= h($pathSteps[0]['copy']) ?></p>
                </div>
                <button type="button" class="pg-home-path-next" data-path-next aria-label="Next step">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6.5 14.5 12 9 17.5"/></svg>
                </button>
            </article>
            <div class="pg-home-path-dots" role="tablist" aria-label="How it works steps">
                <?php foreach ($pathSteps as $index => $step): ?>
                    <button
                        type="button"
                        class="pg-home-path-dot<?= $index === 0 ? ' is-active' : '' ?>"
                        data-path-dot="<?= (int) $index ?>"
                        aria-label="Step <?= (int) ($index + 1) ?>: <?= h($step['label']) ?>"
                        aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                    ></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<section class="section insights" id="insights">
    <div class="wrap">
        <div class="section-head reveal-block">
            <div>
                <div class="eyebrow">The property perspective</div>
                <h2>Make your next move<br>an informed one.</h2>
            </div>
        </div>
        <div class="article-grid">
            <article class="article reveal-item">
                <div class="article-media">
                    <img loading="lazy" src="<?= h(webPath('public/images/insight-pune.jpg')) ?>" alt="Contemporary city buildings">
                </div>
                <div class="category-label">Location guide · Sample topic</div>
                <h3>Choosing the right neighbourhood in Pune</h3>
            </article>
            <article class="article reveal-item">
                <div class="article-media">
                    <img loading="lazy" src="<?= h(webPath('public/images/insight-mumbai.jpg')) ?>" alt="Urban architecture">
                </div>
                <div class="category-label">Buyer essentials · Sample topic</div>
                <h3>Understanding the costs of buying in Mumbai</h3>
            </article>
            <article class="article reveal-item">
                <div class="article-media">
                    <img loading="lazy" src="<?= h(webPath('public/images/villas.jpg')) ?>" alt="Modern residential home">
                </div>
                <div class="category-label">Property basics · Sample topic</div>
                <h3>A practical guide to evaluating a property</h3>
            </article>
        </div>
    </div>
</section>
</main>
