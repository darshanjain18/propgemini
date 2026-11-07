<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/analytics.php';
require_once __DIR__ . '/visit-form.php';
require_once __DIR__ . '/social.php';

$socials = socialLinks();

$items = navItems();
$homeUrl = $items[0]['href'];
$enquire = $items[array_key_last($items)];
$contact = contactDetails();
$year = (int) date('Y');
?>
<footer class="footer">
    <div class="wrap footer-layout">
        <div class="footer-brand">
            <a class="brand" href="<?= h($homeUrl) ?>" aria-label="PropGemini home">
                <img class="brand-mark" src="<?= h(webPath('public/images/logo-mark.png')) ?>" alt="" width="40" height="40" decoding="async">
                <span class="brand-name"><span class="brand-prop">Prop</span><span class="brand-gemini">Gemini</span></span>
            </a>
            <p class="footer-tagline">Real Estate, Perfected.</p>
            <p class="footer-intro">An authentic and transparent consultancy for premium homes and commercial spaces.</p>
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

        <nav class="footer-links" aria-label="Quick links">
            <h4>Quick links</h4>
            <a href="<?= h(webPath()) ?>">Home</a>
            <a href="<?= h(webPath('pages/properties')) ?>">Properties</a>
            <a href="<?= h(webPath('pages/about')) ?>">About</a>
            <a href="<?= h($enquire['href']) ?>">Enquire Now</a>
            <a href="<?= h(webPath('pages/careers')) ?>">Careers</a>
        </nav>

        <div class="footer-links">
            <h4>Cities</h4>
            <a href="<?= h(webPath('pages/properties?city=Pune')) ?>">Pune</a>
            <a href="<?= h(webPath('pages/properties?city=Mumbai')) ?>">Mumbai</a>
            <a href="<?= h(webPath('pages/properties?city=Gurgaon')) ?>">Gurgaon</a>
        </div>

        <div class="footer-links">
            <h4>Resources</h4>
            <a href="<?= h(webPath('pages/news')) ?>">News</a>
            <a href="<?= h(webPath('pages/gallery')) ?>">Gallery</a>
            <a href="<?= h(webPath('pages/emi-calculator')) ?>">EMI Calculator</a>          
        </div>

        <div class="footer-contact">
            <h4>Get in touch</h4>
            <strong>PropGemini</strong>
            <?php if ($contact['has_phone']): ?>
                <a href="tel:<?= h($contact['phone_tel']) ?>"><?= h($contact['phone_display']) ?></a>
            <?php endif; ?>
            <?php if ($contact['has_email']): ?>
                <a href="mailto:<?= h($contact['email']) ?>"><?= h($contact['email']) ?></a>
            <?php endif; ?>
            <p class="footer-cities-line">Pune · Mumbai · Gurgaon</p>
            <?php if ($contact['has_whatsapp']): ?>
                <a class="footer-whatsapp" href="<?= h(whatsappTrackUrl('footer')) ?>" target="_blank" rel="noopener" aria-label="Chat with PropGemini on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
            <?php endif; ?>
            <a class="footer-enquire-link" href="<?= h($enquire['href']) ?>">Start an enquiry <span>↗</span></a>
        </div>
    </div>
    <div class="wrap copyright">
        <span>© <?= $year ?> PropGemini. Developed by Darshan Jain.</span>
        <span class="legal-links">
            <a href="<?= h(webPath('pages/privacy-policy')) ?>">Privacy Policy</a>
            <a href="<?= h(webPath('pages/terms-conditions')) ?>">Terms &amp; Conditions</a>
        </span>
    </div>
</footer>
<div class="toast" id="toast" role="status"></div>
<?php if ($contact['has_whatsapp']): ?>
<a class="wa" href="<?= h(whatsappTrackUrl('float')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
    <i class="fab fa-whatsapp" aria-hidden="true"></i>
</a>
<?php endif; ?>
<?php renderVisitModal(); ?>
<script src="<?= h(webPath('public/js/script.js')) ?>"></script>
<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $scriptSrc): ?>
        <script src="<?= h(webPath((string) $scriptSrc)) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
