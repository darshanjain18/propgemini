<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/visit-form.php';

$pageTitle = 'Enquire Now | PropGemini';
$pageDescription = 'Send an enquiry to PropGemini about a property in Pune, Mumbai or Gurgaon.';
$bodyClass = 'enquire-page';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="enquire-main">
    <div class="enquire-aurora" aria-hidden="true"></div>
    <div class="enquire-mesh" aria-hidden="true"></div>
    <div class="enquire-float enquire-float-a" aria-hidden="true"></div>
    <div class="enquire-float enquire-float-b" aria-hidden="true"></div>
    <div class="enquire-float enquire-float-c" aria-hidden="true"></div>
    <section class="section wrap enquire-layout">
        <div class="enquire-copy">
            <div class="enquire-orbit" aria-hidden="true"><span></span><i></i></div>
            <div class="enquire-kicker"><span></span> Private property advisory</div>
            <h1>Tell us the home you have <em>in mind.</em></h1>
            <p>Share a few details and let our advisors shape a more considered property search around you. Every enquiry is handled with discretion.</p>
            <div class="enquire-details" aria-label="What to expect">
                <div><b>01</b><span>Curated<br>shortlist</span></div>
                <div><b>02</b><span>Private<br>site visits</span></div>
                <div><b>03</b><span>Local market<br>perspective</span></div>
            </div>
            <div class="enquire-signature"><span class="signature-line"></span><span>PropGemini private desk</span></div>
        </div>
        <div class="enquire-card">
            <div class="enquire-card-shine" aria-hidden="true"></div>
            <div class="enquire-card-head">
                <span class="enquire-card-icon" aria-hidden="true">↗</span>
                <div><p class="eyebrow">Start a conversation</p><h2>Share your requirements</h2></div>
                <span class="enquire-card-step">01 <i>/</i> 01</span>
            </div>
            <div class="enquire-form-shell">
                <?php renderVisitFields('page', 'enquire'); ?>
            </div>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
