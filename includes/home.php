<?php
declare(strict_types=1);

require_once __DIR__ . '/properties.php';

$propertiesUrl = webPath('pages/properties.php');
?>
<main>
<section class="hero" id="home">
    <img class="hero-img reveal-media" src="<?= h(webPath('public/images/hero.jpg')) ?>" alt="Architect-designed residence surrounded by landscaped grounds">
    <div class="wrap hero-content">
        <div class="eyebrow reveal-item" data-reveal-delay="0">Property, reimagined</div>
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
<section class="section wrap" id="amenities">
    <div class="section-head reveal-block"><div><div class="eyebrow">Living</div><h2>Amenities that elevate everyday life</h2></div></div>
    <div class="amenity-grid">
        <article class="amenity reveal-item"><span>01</span><h3>Clubhouse</h3><p>Lounges, indoor games and space for gatherings.</p></article>
        <article class="amenity reveal-item"><span>02</span><h3>Landscaped gardens</h3><p>Green courtyards and walking paths for everyday calm.</p></article>
        <article class="amenity reveal-item"><span>03</span><h3>Fitness &amp; wellness</h3><p>Gym and open-air spaces for yoga and training.</p></article>
        <article class="amenity reveal-item"><span>04</span><h3>Children's play</h3><p>Safe play areas designed for different age groups.</p></article>
        <article class="amenity reveal-item"><span>05</span><h3>Security</h3><p>Controlled access and monitored entry points.</p></article>
        <article class="amenity reveal-item"><span>06</span><h3>Parking &amp; power</h3><p>Visitor parking and power backup for everyday ease.</p></article>
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
