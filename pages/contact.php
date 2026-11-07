<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/social.php';
require_once dirname(__DIR__) . '/includes/analytics.php';

$status = $_GET['status'] ?? '';
$subject = trim((string) ($_GET['subject'] ?? ''));
if (strlen($subject) > 200) {
    $subject = '';
}
$contact = contactDetails();
$pageTitle = 'Contact PropGemini';
$pageDescription = 'Send a general enquiry to PropGemini about residential and commercial property in Pune and Mumbai.';
$bodyClass = 'contact-page';
$pageStyles = ['public/css/contact.css'];
$pageScripts = ['public/js/contact.js'];

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="pg-contact">
    <section class="pg-contact-hero" id="contact" aria-labelledby="pg-contact-heading">
        <div class="pg-contact-waves" aria-hidden="true">
            <svg class="pg-contact-wave pg-contact-wave-left" viewBox="0 0 420 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 140C70 90 110 40 170 55C230 70 250 130 310 120C370 110 390 60 410 40" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                <path d="M20 160C85 115 120 70 175 80C235 92 255 145 320 138C375 132 395 90 410 75" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".55"/>
            </svg>
            <svg class="pg-contact-wave pg-contact-wave-right" viewBox="0 0 420 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 40C70 55 110 120 170 130C230 140 250 80 310 70C370 60 390 100 410 120" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                <path d="M20 65C85 75 120 135 175 145C235 156 255 100 320 92C375 85 395 120 410 140" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".55"/>
            </svg>
        </div>

        <div class="wrap pg-contact-layout">
            <div class="pg-contact-intro pg-contact-reveal" data-reveal>
                <p class="pg-contact-kicker">Get in touch</p>
                <h1 id="pg-contact-heading">Connect with our experts.</h1>
                <p class="pg-contact-lead">Let's discuss your goals, explore the right opportunities and find a home that fits your vision.</p>

                <div class="pg-contact-meta">
                    <?php if ($contact['has_phone']): ?>
                        <a href="tel:<?= h($contact['phone_tel']) ?>"><?= h($contact['phone_display']) ?></a>
                    <?php endif; ?>
                    <?php if ($contact['has_email']): ?>
                        <a href="mailto:<?= h($contact['email']) ?>"><?= h($contact['email']) ?></a>
                    <?php endif; ?>
                    <?php if ($contact['has_whatsapp']): ?>
                        <a href="<?= h(whatsappTrackUrl('contact')) ?>" target="_blank" rel="noopener">WhatsApp</a>
                    <?php endif; ?>
                    <?php if ($contact['has_address']): ?>
                        <p><?= h($contact['address']) ?></p>
                    <?php endif; ?>
                </div>

                <?php $socials = socialLinks(); ?>
                <?php if ($socials !== []): ?>
                    <div class="pg-contact-socials" aria-label="Social media">
                        <?php foreach ($socials as $social): ?>
                            <a href="<?= h($social['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= h($social['label']) ?>">
                                <?= socialIcon((string) $social['id']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pg-contact-collage pg-contact-reveal" data-reveal data-reveal-delay="1" aria-hidden="true">
                <figure class="pg-contact-shot pg-contact-shot-tower">
                    <img src="<?= h(webPath('public/images/projects/pune-tower-1.jpg')) ?>" alt="" width="640" height="900" loading="eager" decoding="async">
                </figure>
                <figure class="pg-contact-shot pg-contact-shot-interior">
                    <img src="<?= h(webPath('public/images/property-spotlight-luxury-interior.jpg')) ?>" alt="" width="640" height="640" loading="eager" decoding="async">
                </figure>
                <figure class="pg-contact-shot pg-contact-shot-pool">
                    <img src="<?= h(webPath('public/images/projects/pool-amenity.jpg')) ?>" alt="" width="720" height="480" loading="eager" decoding="async">
                </figure>
            </div>

            <form class="pg-contact-form pg-contact-reveal" data-reveal data-reveal-delay="2" action="<?= h(webPath('actions/contact_submit.php')) ?>" method="POST" novalidate>
                <h2 class="pg-contact-form-title">Send us an <em>Enquiry</em></h2>

                <?php if ($status === 'success'): ?>
                    <p class="form-status pg-contact-status" role="status">Your enquiry has been submitted successfully. Our team will follow up shortly.</p>
                <?php elseif ($status === 'error'): ?>
                    <p class="form-status error pg-contact-status" role="alert">Please check your details and try again.</p>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="hp-field" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="pg-contact-fields">
                    <div class="pg-contact-field">
                        <label for="name">Full Name *</label>
                        <div class="pg-contact-input">
                            <span class="pg-contact-icon" aria-hidden="true"><i class="fa-regular fa-user"></i></span>
                            <input id="name" name="name" type="text" maxlength="100" autocomplete="name" required placeholder="Your full name">
                        </div>
                    </div>
                    <div class="pg-contact-field">
                        <label for="phone">Mobile Number *</label>
                        <div class="pg-contact-input">
                            <span class="pg-contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                            <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="10" pattern="\d{10}" autocomplete="tel" required placeholder="10-digit mobile">
                        </div>
                    </div>
                    <div class="pg-contact-field">
                        <label for="email">Email Address *</label>
                        <div class="pg-contact-input">
                            <span class="pg-contact-icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                            <input id="email" name="email" type="email" maxlength="254" autocomplete="email" required placeholder="name@email.com">
                        </div>
                    </div>
                    <div class="pg-contact-field">
                        <label for="subject">Subject</label>
                        <div class="pg-contact-input">
                            <span class="pg-contact-icon" aria-hidden="true"><i class="fa-solid fa-list-ul"></i></span>
                            <input id="subject" name="subject" type="text" maxlength="200" value="<?= h($subject) ?>" placeholder="How can we help?">
                        </div>
                    </div>
                    <div class="pg-contact-field pg-contact-field-full">
                        <label for="message">Your Enquiry *</label>
                        <div class="pg-contact-input pg-contact-input-area">
                            <textarea id="message" name="message" maxlength="5000" required placeholder="Location, configuration, timeline or anything else you'd like to share"></textarea>
                        </div>
                    </div>
                </div>

                <label class="pg-contact-consent">
                    <input type="checkbox" name="whatsapp_consent" value="1">
                    <span>I agree to receive WhatsApp follow-up messages about my enquiry.</span>
                </label>

                <button class="pg-contact-submit" type="submit">Submit Enquiry <span aria-hidden="true">↗</span></button>
            </form>
        </div>

        <div class="wrap">
            <ul class="pg-contact-stats" aria-label="PropGemini highlights">
                <li class="pg-contact-stat" data-reveal data-reveal-delay="0">
                    <span class="pg-contact-stat-icon" aria-hidden="true"><i class="fa-regular fa-building"></i></span>
                    <span class="pg-contact-stat-label">3 Cities</span>
                </li>
                <li class="pg-contact-stat" data-reveal data-reveal-delay="1">
                    <span class="pg-contact-stat-icon" aria-hidden="true"><i class="fa-regular fa-handshake"></i></span>
                    <span class="pg-contact-stat-label">500+ Happy Clients</span>
                </li>
                <li class="pg-contact-stat" data-reveal data-reveal-delay="2">
                    <span class="pg-contact-stat-icon" aria-hidden="true"><i class="fa-solid fa-award"></i></span>
                    <span class="pg-contact-stat-label">RERA Compliant Projects</span>
                </li>
                <li class="pg-contact-stat" data-reveal data-reveal-delay="3">
                    <span class="pg-contact-stat-icon" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
                    <span class="pg-contact-stat-label">Dedicated Relationship Manager</span>
                </li>
            </ul>
        </div>
    </section>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
