<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';

$pageTitle = 'About PropGemini';
$pageDescription = 'Your trusted partner in luxury real estate across Pune, Mumbai and Gurgaon.';
$bodyClass = 'about-page';
$aboutBlurb = siteSetting(
    'about_blurb',
    'PropGemini curates premium residences across India’s key cities for discerning homebuyers and investors.'
);

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="about-main">
    <section class="section wrap about-intro">
        <div class="about-intro-copy">
            <p class="eyebrow">About PropGemini</p>
            <h2>Your trusted partner in <em>luxury real estate</em></h2>
            <p><?= h($aboutBlurb) ?></p>
            <p class="about-lede">Our curated portfolio features refined homes from reputable developers, with clear guidance from first enquiry through site visit.</p>
            <div class="about-signature"><span></span>PropGemini private desk</div>
        </div>
        <div class="about-visual">
            <div class="about-orbit" aria-hidden="true"></div>
            <figure class="about-frame about-frame-a">
                <img src="<?= h(webPath('public/images/hero.jpg')) ?>" alt="Luxury residential property" loading="lazy">
            </figure>
            <figure class="about-frame about-frame-b">
                <img src="<?= h(webPath('public/images/meridian.jpg')) ?>" alt="Premium real estate development" loading="lazy">
            </figure>
        </div>
    </section>

    <section class="section wrap about-difference">
        <div class="about-difference-head">
            <p class="eyebrow">The PropGemini difference</p>
            <h2>Confidence in every step</h2>
            <p>A calm, considered process built around transparency, location quality and end-to-end support.</p>
        </div>
        <div class="about-card-grid">
            <article class="about-glass-card">
                <span class="about-card-icon" aria-hidden="true">✓</span>
                <h3>RERA-aware guidance</h3>
                <p>Explore registered projects with clearer paperwork and project context.</p>
            </article>
            <article class="about-glass-card">
                <span class="about-card-icon" aria-hidden="true">☆</span>
                <h3>Premium locations</h3>
                <p>Homes in sought-after neighbourhoods across Pune, Mumbai and Gurgaon.</p>
            </article>
            <article class="about-glass-card">
                <span class="about-card-icon" aria-hidden="true">⌂</span>
                <h3>Verified listings</h3>
                <p>Curated inventory with relevant project details before you visit.</p>
            </article>
            <article class="about-glass-card">
                <span class="about-card-icon" aria-hidden="true">↗</span>
                <h3>End-to-end support</h3>
                <p>From enquiry and shortlist to private site visits with our advisors.</p>
            </article>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
