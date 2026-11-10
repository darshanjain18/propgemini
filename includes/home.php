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
        <p class="reveal-item" data-reveal-delay="2">A considered collection of remarkable residences across Pune, Mumbai and Gurgaon. Discover architecture, addresses and spaces chosen for the way you want to live.</p>
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
            <button class="rail-btn" type="button" id="propertyPrev" aria-label="Scroll properties left">←</button>
            <button class="rail-btn" type="button" id="propertyNext" aria-label="Scroll properties right">→</button>
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
                <div class="eyebrow">Pune, Mumbai and Gurgaon</div>
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
                <span class="pg-home-amenities-divider" aria-hidden="true"></span>
                <p class="pg-home-amenities-lead">Thoughtfully curated spaces for wellness, leisure, family and peace of mind.</p>
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
<section class="section wrap">
    <div class="section-head reveal-block"><div><div class="eyebrow">Explore by intention</div><h2>A place for every plan.</h2></div></div>
    <div class="categories">
        <a href="<?= h($propertiesUrl . '?type=Apartment') ?>" class="category reveal-item"><span class="num">01 / RESIDENTIAL</span><h3>Luxury apartments</h3><span class="go">↗</span></a>
        <a href="<?= h($propertiesUrl . '?type=Villa') ?>" class="category reveal-item"><span class="num">02 / PRIVATE LIVING</span><h3>Villas &amp; homes</h3><span class="go">↗</span></a>
        <a href="<?= h($propertiesUrl . '?type=Apartment') ?>" class="category reveal-item"><span class="num">03 / ELEVATED LIVING</span><h3>Penthouses</h3><span class="go">↗</span></a>
        <a href="<?= h($propertiesUrl . '?type=Commercial') ?>" class="category reveal-item"><span class="num">04 / BUSINESS</span><h3>Commercial spaces</h3><span class="go">↗</span></a>
        <a href="<?= h($propertiesUrl . '?type=Commercial') ?>" class="category reveal-item"><span class="num">05 / WORKPLACE</span><h3>Office properties</h3><span class="go">↗</span></a>
        <a href="<?= h(webPath('pages/enquire.php')) ?>" class="category reveal-item"><span class="num">06 / YOUR NEXT MOVE</span><h3>Investment enquiries</h3><span class="go">↗</span></a>
    </div>
</section>
<section class="section why" id="about">
    <div class="why-motion" aria-hidden="true">
        <div class="enquire-aurora"></div>
        <div class="enquire-mesh"></div>
        <div class="enquire-float enquire-float-a"></div>
        <div class="enquire-float enquire-float-b"></div>
        <div class="enquire-float enquire-float-c"></div>
    </div>
    <div class="wrap why-grid">
        <div class="why-image reveal-media">
            <img loading="lazy" src="<?= h(webPath('public/images/meridian.jpg')) ?>" alt="Calm contemporary interior">
            <div class="image-note">A better way<br>to decide.</div>
        </div>
        <div>
            <div class="eyebrow reveal-item">The PropGemini approach</div>
            <h2 class="reveal-item">More than a property search.</h2>
            <div class="benefits">
                <div class="benefit reveal-item"><span class="num">01 — DISCOVER</span><h3>Curated opportunities</h3><p>Explore relevant options without unnecessary clutter.</p></div>
                <div class="benefit reveal-item"><span class="num">02 — UNDERSTAND</span><h3>Local perspective</h3><p>Consider neighbourhoods, connectivity and everyday convenience.</p></div>
                <div class="benefit reveal-item"><span class="num">03 — PERSONALISE</span><h3>Guidance that listens</h3><p>Focus the search around your needs, budget and priorities.</p></div>
                <div class="benefit reveal-item"><span class="num">04 — MOVE FORWARD</span><h3>Support at each step</h3><p>Get help navigating the enquiry and property discovery process.</p></div>
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
            <h2>Room to live<br><em>beautifully.</em></h2>
            <p>A considered residence framed by quiet interiors, generous light and a neighbourhood that suits everyday living.</p>
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
<section class="section wrap">
    <div class="reveal-block">
        <div class="eyebrow">A clearer path forward</div>
        <h2>From first search<br>to the right decision.</h2>
    </div>
    <div class="steps">
        <div class="step reveal-item"><div class="n">01</div><h3>Tell us what you need</h3><p>Share your preferred location, budget and requirements.</p></div>
        <div class="step reveal-item"><div class="n">02</div><h3>Explore relevant options</h3><p>Review properties aligned with your priorities.</p></div>
        <div class="step reveal-item"><div class="n">03</div><h3>Build your shortlist</h3><p>Compare the details that matter to your decision.</p></div>
        <div class="step reveal-item"><div class="n">04</div><h3>Move forward with clarity</h3><p>Connect with an advisor to discuss your next steps.</p></div>
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
