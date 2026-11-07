<?php
declare(strict_types=1);

/**
 * Shared legal document chrome.
 *
 * Expected variables:
 * - $legalPageKey: 'privacy'|'terms'
 * - $legal: array from legalPageContent()
 * - $legalDoc: array from parseLegalDocument()
 * - $legalSubtitle: string
 * - $legalHeroImage: web-relative image path
 * - $legalCalloutTitle: string
 * - $legalCalloutBody: string
 * - $legalContactTitle: string
 * - $legalContactLead: string
 * - $contact: contactDetails() array
 */

$legalPageKey = $legalPageKey ?? 'privacy';
$legalSubtitle = $legalSubtitle ?? '';
$legalHeroImage = $legalHeroImage ?? 'public/images/projects/pune-tower-1.jpg';
$legalCalloutTitle = $legalCalloutTitle ?? 'In short';
$legalCalloutBody = $legalCalloutBody ?? '';
$legalContactTitle = $legalContactTitle ?? 'Questions?';
$legalContactLead = $legalContactLead ?? '';
$contact = $contact ?? contactDetails();
$contactUrl = webPath('pages/contact');
$enquireUrl = webPath('pages/enquire');

$updatedShort = trim((string) ($legalDoc['updated_short'] ?? ''));
if ($updatedShort === '' && !empty($legal['updated_at'])) {
    $ts = strtotime((string) $legal['updated_at']);
    if ($ts !== false) {
        $updatedShort = date('F Y', $ts);
    }
}
if ($updatedShort === '') {
    $updatedShort = 'November 2026';
}

$isPrivacy = $legalPageKey === 'privacy';
?>
<main class="pg-legal">
    <section class="pg-legal-hero" aria-labelledby="pg-legal-title">
        <div class="pg-legal-hero-waves" aria-hidden="true">
            <svg class="pg-legal-hero-wave pg-legal-hero-wave-a" viewBox="0 0 460 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 150C70 100 120 40 185 55C250 70 270 130 335 120C390 112 420 70 450 48" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                <path d="M16 172C85 128 135 75 195 88C260 102 280 155 345 148C400 142 425 100 450 82" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".55"/>
            </svg>
            <svg class="pg-legal-hero-wave pg-legal-hero-wave-b" viewBox="0 0 280 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 90C50 55 90 20 140 30C190 40 210 85 250 78" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="wrap pg-legal-hero-inner">
            <div data-legal-reveal>
                <p class="pg-legal-kicker">PropGemini</p>
                <h1 id="pg-legal-title"><?= h($legal['title']) ?></h1>
                <?php if ($legalSubtitle !== ''): ?>
                    <p class="pg-legal-hero-lead"><?= h($legalSubtitle) ?></p>
                <?php endif; ?>
            </div>

            <figure class="pg-legal-hero-visual" data-legal-reveal data-legal-delay="1">
                <img
                    src="<?= h(webPath($legalHeroImage)) ?>"
                    alt=""
                    width="900"
                    height="720"
                    loading="eager"
                    decoding="async"
                >
                <div class="pg-legal-hero-badge" aria-hidden="true">
                    <?php if ($isPrivacy): ?>
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3 5 6.2v5.3c0 4.3 2.9 8.2 7 9.5 4.1-1.3 7-5.2 7-9.5V6.2L12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <rect x="9.2" y="10.2" width="5.6" height="4.8" rx="1.1" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M10.4 10.2V9.1a1.6 1.6 0 0 1 3.2 0v1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 3.8h7.2L19 8.4V20.2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.8a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M14.1 3.8V8.4H19" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M9 12.2h6M9 15.4h6M9 9h3.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    <?php endif; ?>
                </div>
            </figure>
        </div>
    </section>

    <section class="pg-legal-doc" aria-label="<?= h($legal['title']) ?> content">
        <div class="wrap pg-legal-layout">
            <aside class="pg-legal-toc" data-legal-toc data-legal-reveal>
                <h2 class="pg-legal-toc-head">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 5.5h6.5A2.5 2.5 0 0 1 14 8v12.2H7.2A2.2 2.2 0 0 1 5 18V5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M19 5.5h-6.5A2.5 2.5 0 0 0 10 8v12.2h6.8A2.2 2.2 0 0 0 19 18V5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                    On This Page
                </h2>

                <button
                    class="pg-legal-toc-toggle"
                    type="button"
                    data-legal-toc-toggle
                    aria-expanded="false"
                    aria-controls="pg-legal-toc-list"
                >
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M5 5.5h6.5A2.5 2.5 0 0 1 14 8v12.2H7.2A2.2 2.2 0 0 1 5 18V5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M19 5.5h-6.5A2.5 2.5 0 0 0 10 8v12.2h6.8A2.2 2.2 0 0 0 19 18V5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        </svg>
                        On This Page
                    </span>
                    <svg class="pg-legal-toc-chevron" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <ol class="pg-legal-toc-list" id="pg-legal-toc-list">
                    <?php foreach ($legalDoc['sections'] as $section): ?>
                        <li>
                            <a href="#<?= h($section['id']) ?>" data-legal-toc-link>
                                <span class="pg-legal-toc-num"><?= h($section['number']) ?>.</span>
                                <span><?= h($section['title']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </aside>

            <div class="pg-legal-content" data-legal-reveal data-legal-delay="1">
                <p class="pg-legal-meta">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect x="3.5" y="5" width="17" height="15.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M8 3.5v3M16 3.5v3M3.5 10h17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Last Updated: <?= h($updatedShort) ?>
                </p>

                <?php if ($legalCalloutBody !== ''): ?>
                    <aside class="pg-legal-callout" aria-label="<?= h($legalCalloutTitle) ?>">
                        <span class="pg-legal-callout-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M12 10.6V16M12 7.8h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <div>
                            <strong><?= h($legalCalloutTitle) ?></strong>
                            <p><?= h($legalCalloutBody) ?></p>
                        </div>
                    </aside>
                <?php endif; ?>

                <?php if ($legalDoc['preamble'] !== []): ?>
                    <div class="pg-legal-preamble">
                        <?php foreach ($legalDoc['preamble'] as $paragraph): ?>
                            <p><?= nl2br(h($paragraph), false) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($legalDoc['sections'] as $section): ?>
                    <article class="pg-legal-section" id="<?= h($section['id']) ?>">
                        <h2><?= h($section['heading']) ?></h2>
                        <div class="pg-legal-divider" aria-hidden="true"></div>
                        <?php foreach ($section['paragraphs'] as $paragraph): ?>
                            <p><?= nl2br(h($paragraph), false) ?></p>
                        <?php endforeach; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="wrap">
        <div class="pg-legal-contact" data-legal-reveal>
            <div class="pg-legal-contact-inner">
                <div>
                    <h2><?= h($legalContactTitle) ?></h2>
                    <?php if ($legalContactLead !== ''): ?>
                        <p><?= h($legalContactLead) ?></p>
                    <?php endif; ?>
                </div>
                <div class="pg-legal-contact-actions">
                    <?php if (!empty($contact['has_email'])): ?>
                        <a class="pg-legal-btn pg-legal-btn-primary" href="mailto:<?= h($contact['email']) ?>">
                            <?= h($contact['email']) ?>
                        </a>
                    <?php else: ?>
                        <a class="pg-legal-btn pg-legal-btn-primary" href="mailto:hello@propgemini.com">hello@propgemini.com</a>
                    <?php endif; ?>
                    <a class="pg-legal-btn pg-legal-btn-outline" href="<?= h($contactUrl) ?>">Contact us</a>
                    <a class="pg-legal-btn pg-legal-btn-outline" href="<?= h($enquireUrl) ?>">Enquire</a>
                </div>
            </div>
        </div>
    </section>
</main>
