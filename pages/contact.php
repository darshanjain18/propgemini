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

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>

<main>
    <section class="pg-section" id="contact">
        <div class="wrap pg-apply">
            <div>
                <p class="pg-kicker">Desk</p>
                <h2>We reply with a relevant shortlist.</h2>
                <div class="contact-aside">
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
                    <?php $socials = socialLinks(); ?>
                    <?php if ($socials !== []): ?>
                        <div class="footer-socials" aria-label="Social media">
                            <?php foreach ($socials as $social): ?>
                                <a href="<?= h($social['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= h($social['label']) ?>">
                                    <?= socialIcon((string) $social['id']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <form class="pg-form" action="<?= h(webPath('actions/contact_submit.php')) ?>" method="POST">
                <?php if ($status === 'success'): ?>
                    <p class="form-status" role="status">Your enquiry has been submitted successfully. Our team will follow up shortly.</p>
                <?php elseif ($status === 'error'): ?>
                    <p class="form-status error" role="alert">Please check your details and try again.</p>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="hp-field" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="name">Full name *</label>
                        <input id="name" name="name" type="text" maxlength="100" autocomplete="name" required>
                    </div>
                    <div class="field">
                        <label for="phone">Mobile number *</label>
                        <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="10" pattern="\d{10}" autocomplete="tel" required>
                    </div>
                    <div class="field">
                        <label for="email">Email address *</label>
                        <input id="email" name="email" type="email" maxlength="254" autocomplete="email" required>
                    </div>
                    <div class="field">
                        <label for="subject">Subject</label>
                        <input id="subject" name="subject" type="text" maxlength="200" value="<?= h($subject) ?>">
                    </div>
                    <div class="field full">
                        <label for="message">Your enquiry *</label>
                        <textarea id="message" name="message" maxlength="5000" required placeholder="Location, configuration, timeline or anything else you'd like to share"></textarea>
                    </div>
                </div>

                <label class="consent">
                    <input type="checkbox" name="whatsapp_consent" value="1">
                    <span>I agree to receive WhatsApp follow-up messages about my enquiry.</span>
                </label>

                <button class="btn light" type="submit">Submit enquiry ↗</button>
            </form>
        </div>
    </section>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
