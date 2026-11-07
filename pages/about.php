<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';

$pageTitle = 'About PropGemini';
$pageDescription = 'Your trusted partner in luxury real estate across Pune, Mumbai and Gurgaon.';
$bodyClass = 'about-page';
$pageStyles = ['public/css/about-difference.css'];
$pageScripts = ['public/js/about-difference.js'];
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

    <section class="pg-diff" id="propgemini-difference" aria-labelledby="pg-diff-heading">
        <div class="pg-diff-arcs" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>
        <div class="wrap pg-diff-inner">
            <header class="pg-diff-head">
                <p class="pg-diff-eyebrow">The PropGemini Difference</p>
                <h2 id="pg-diff-heading">Confidence in every step</h2>
                <p class="pg-diff-lead">A calm, considered process built around transparency, location quality and end-to-end support.</p>
            </header>

            <ul class="pg-diff-features">
                <li class="pg-diff-item" data-diff-reveal style="--diff-delay: 0ms">
                    <span class="pg-diff-icon" aria-hidden="true">
                        <span class="pg-diff-ring"></span>
                        <span class="pg-diff-circle">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 8h14l8 8v24H14V8Z"/>
                                <path d="M28 8v8h8"/>
                                <circle cx="23.5" cy="27.5" r="5.5"/>
                                <path d="m27.4 31.4 4.2 4.2"/>
                            </svg>
                        </span>
                    </span>
                    <h3>RERA-aware guidance</h3>
                    <p>Explore registered projects with clearer paperwork and project context.</p>
                </li>
                <li class="pg-diff-item" data-diff-reveal style="--diff-delay: 90ms">
                    <span class="pg-diff-icon" aria-hidden="true">
                        <span class="pg-diff-ring"></span>
                        <span class="pg-diff-circle">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M24 42s-12.5-10.6-12.5-20A12.5 12.5 0 0 1 24 9.5a12.5 12.5 0 0 1 12.5 12.5C36.5 31.4 24 42 24 42Z"/>
                                <circle cx="24" cy="21.8" r="4.4"/>
                            </svg>
                        </span>
                    </span>
                    <h3>Premium locations</h3>
                    <p>Homes in sought-after neighbourhoods across Pune, Mumbai and Gurgaon.</p>
                </li>
                <li class="pg-diff-item" data-diff-reveal style="--diff-delay: 180ms">
                    <span class="pg-diff-icon" aria-hidden="true">
                        <span class="pg-diff-ring"></span>
                        <span class="pg-diff-circle">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 22.5 24 10.5l15 12V38.5H9v-16Z"/>
                                <path d="M19 38.5v-11h10v11"/>
                            </svg>
                        </span>
                    </span>
                    <h3>Verified listings</h3>
                    <p>Curated inventory with relevant project details before you visit.</p>
                </li>
                <li class="pg-diff-item" data-diff-reveal style="--diff-delay: 270ms">
                    <span class="pg-diff-icon" aria-hidden="true">
                        <span class="pg-diff-ring"></span>
                        <span class="pg-diff-circle">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16.5 29.5v2.4a7.5 7.5 0 0 0 15 0v-2.4"/>
                                <path d="M14 23.2V20a10 10 0 0 1 20 0v3.2"/>
                                <rect x="9.5" y="22.5" width="6.2" height="8.4" rx="3"/>
                                <rect x="32.3" y="22.5" width="6.2" height="8.4" rx="3"/>
                            </svg>
                        </span>
                    </span>
                    <h3>End-to-end support</h3>
                    <p>From enquiry and shortlist to private site visits with our advisors.</p>
                </li>
            </ul>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
