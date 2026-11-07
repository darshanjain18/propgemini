<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/visit-form.php';

$pageTitle = 'Enquire Now | PropGemini';
$pageDescription = 'Tell us what you’re looking for and our experts will guide you with curated options across Pune, Mumbai and Gurgaon.';
$bodyClass = 'enquire-page';
$pageStyles = ['public/css/enquire.css'];

$heroImage = 'public/images/enquire-now.jpg';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="pg-enquire">
    <section
        class="pg-enquire-hero"
        style="--pg-enquire-bg: url('<?= h(webPath($heroImage)) ?>')"
        aria-labelledby="pg-enquire-heading"
    >
        <div class="pg-enquire-veil" aria-hidden="true"></div>
        <div class="wrap pg-enquire-layout">
            <div class="pg-enquire-copy">
                <p class="pg-enquire-kicker">Enquire Now</p>
                <h1 id="pg-enquire-heading">
                    Find Your<br>
                    Next Home<br>
                    <em>With Us.</em>
                </h1>
                <p class="pg-enquire-lead">
                    Tell us what you’re looking for and our experts will guide you with curated options.
                </p>
                <ul class="pg-enquire-highlights" aria-label="What to expect">
                    <li>
                        <span class="pg-enquire-hi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4.5 10.5 12 4.5l7.5 6V19a1.5 1.5 0 0 1-1.5 1.5h-3.2v-5.2H9.2V20.5H6A1.5 1.5 0 0 1 4.5 19z"/></svg>
                        </span>
                        <span>Curated Shortlist</span>
                    </li>
                    <li>
                        <span class="pg-enquire-hi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4.8 19.2V6.5A1.7 1.7 0 0 1 6.5 4.8h11a1.7 1.7 0 0 1 1.7 1.7v12.7"/><path d="M8.2 9.2h7.6M8.2 12.4h7.6M8.2 15.6h4.4"/><path d="M4.8 19.2h14.4"/></svg>
                        </span>
                        <span>Private Site Visits</span>
                    </li>
                    <li>
                        <span class="pg-enquire-hi-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="7.2"/><path d="M12 4.8v14.4M4.8 12h14.4"/><path d="M7.2 7.2c2.1 1.8 5.5 1.8 7.6 0M7.2 16.8c2.1-1.8 5.5-1.8 7.6 0"/></svg>
                        </span>
                        <span>Local Market Perspective</span>
                    </li>
                </ul>
            </div>

            <div class="pg-enquire-card">
                <div class="pg-enquire-card-head">
                    <span class="pg-enquire-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4.5 10.8 12 4.6l7.5 6.2V19a1.6 1.6 0 0 1-1.6 1.6h-3.4v-5.4H9.5V20.6H6.1A1.6 1.6 0 0 1 4.5 19z"/></svg>
                    </span>
                    <h2>Share Your Requirements</h2>
                </div>
                <?php
                renderVisitFields('page', 'enquire', '', [
                    'form_class' => 'pg-enquire-form',
                    'labels' => [
                        'name' => 'Full Name',
                        'phone' => 'Contact Number',
                        'email' => 'Email Address',
                        'city' => 'City',
                        'property' => 'Property',
                        'message' => 'Message',
                    ],
                    'placeholders' => [
                        'name' => 'Enter your name',
                        'phone' => 'Enter number',
                        'email' => 'Enter your email',
                        'property' => 'Search property...',
                        'message' => 'Tell us your requirements...',
                    ],
                    'show_city' => true,
                    'cities' => ['Pune', 'Mumbai', 'Gurgaon'],
                    'button' => 'Send Enquiry <span aria-hidden="true">↗</span>',
                    'required_marks' => true,
                ]);
                ?>
            </div>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
