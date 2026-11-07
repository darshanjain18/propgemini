<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/careers.php';
require_once dirname(__DIR__) . '/includes/properties.php';

$pageTitle = 'Careers at PropGemini';
$pageDescription = 'Build a rewarding career with PropGemini. Join a real estate team that values people, ideas and long-term growth.';
$bodyClass = 'careers-page';
$pageStyles = ['public/css/careers.css'];
$pageScripts = ['public/js/careers.js'];

$status = (string) ($_GET['status'] ?? '');
$roles = careerRoleOptions();
$experienceOptions = careerExperienceOptions();
$noticeOptions = careerNoticeOptions();

$heroImage = 'public/images/projects/luxury-lobby.jpg';
if (!is_file(dirname(__DIR__) . '/' . $heroImage)) {
    $heroImage = 'public/images/property-spotlight-luxury-interior.jpg';
}

ensureCareerSchema();

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>
    <section class="pg-careers-hero-section" aria-labelledby="pg-careers-hero-heading">
        <div class="wrap">
            <div class="pg-careers-hero">
                <svg class="pg-careers-hero-waves" viewBox="0 0 900 320" fill="none" aria-hidden="true" focusable="false">
                    <path class="pg-careers-hero-wave pg-careers-hero-wave--left" d="M20 250C110 190 180 150 270 170C380 196 430 270 540 250C650 230 700 140 860 120" stroke="currentColor" stroke-width="1.4"/>
                    <path class="pg-careers-hero-wave pg-careers-hero-wave--left" d="M10 285C105 235 175 205 265 220C375 240 440 300 550 275C660 250 730 165 880 145" stroke="currentColor" stroke-width="1" opacity="0.55"/>
                    <path class="pg-careers-hero-wave pg-careers-hero-wave--mid" d="M420 40C500 70 560 120 620 110C700 96 740 40 820 55" stroke="currentColor" stroke-width="1.2" opacity="0.65"/>
                </svg>
                <div class="pg-careers-hero-copy">
                    <p class="pg-careers-kicker">Careers at PropGemini</p>
                    <h1 id="pg-careers-hero-heading">Build a Rewarding Career <em>with Us.</em></h1>
                    <p class="pg-careers-hero-lead">Be part of a growing real estate team that values people, ideas and long-term growth.</p>
                </div>
                <figure class="pg-careers-hero-visual">
                    <img
                        src="<?= h(webPath($heroImage)) ?>"
                        alt="Luxury PropGemini workspace interior"
                        width="960"
                        height="720"
                        decoding="async"
                    >
                </figure>
            </div>
        </div>
    </section>

    <section class="pg-careers-why" aria-label="Why join PropGemini">
        <div class="wrap">
            <div class="pg-careers-benefits-card">
                <article class="pg-careers-benefit">
                    <span class="pg-careers-benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="8.5" cy="9" r="2.2"/><circle cx="15.5" cy="9" r="2.2"/><circle cx="12" cy="14.2" r="2.2"/><path d="M5.2 17.2c1.1-1.6 2.5-2.4 4-2.4M14.8 14.8c1.4.2 2.7 1 3.8 2.4"/></svg>
                    </span>
                    <h3>Collaborative Culture</h3>
                    <p>Work with a supportive and high-performing team.</p>
                </article>
                <article class="pg-careers-benefit">
                    <span class="pg-careers-benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M5 18V10M10 18V8M15 18V5M20 18v-6"/><path d="m16.5 5.5 3.5-1 .8 3.2"/></svg>
                    </span>
                    <h3>Professional Growth</h3>
                    <p>Learn, upskill and take on new opportunities.</p>
                </article>
                <article class="pg-careers-benefit">
                    <span class="pg-careers-benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="m12 7.2 1.2 2.6 2.9.3-2.2 2 .6 2.8L12 13.5 9.5 14.9l.6-2.8-2.2-2 2.9-.3z"/></svg>
                    </span>
                    <h3>Meaningful Work</h3>
                    <p>Contribute to projects that create real impact.</p>
                </article>
                <article class="pg-careers-benefit">
                    <span class="pg-careers-benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M8 15c0-2.8 1.8-4.5 4-4.5s4 1.7 4 4.5"/><path d="M12 10.5c1.3-1.7 3.4-2.2 4.9-1"/><path d="M9 17.5h6"/><path d="M10.4 17.5c.4 1.2 1 1.8 1.6 1.8s1.2-.6 1.6-1.8"/></svg>
                    </span>
                    <h3>People-First Environment</h3>
                    <p>A balanced, inclusive and respectful workplace.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="pg-careers-apply" id="apply" aria-labelledby="pg-careers-apply-heading">
        <div class="wrap pg-careers-apply-grid">
            <div class="pg-careers-apply-copy">
                <p class="pg-careers-kicker">Apply Now</p>
                <h2 id="pg-careers-apply-heading">Tell us about yourself.</h2>
                <p>Share a few details and let us know the role you’re interested in. We’ll get in touch if there’s a match.</p>
                <div class="pg-careers-sketch" aria-hidden="true">
                    <svg viewBox="0 0 240 130" fill="none">
                        <path d="M16 108V42l28-14 22 11v69" stroke="currentColor" stroke-width="1.2"/>
                        <path d="M44 38v70M66 50v58" stroke="currentColor" stroke-width="1"/>
                        <path d="M26 58h10M26 70h10M26 82h10M52 60h10M52 72h10" stroke="currentColor" stroke-width="1"/>
                        <path d="M92 108V52l34-12 30 14v54" stroke="currentColor" stroke-width="1.2"/>
                        <path d="M110 58h12M110 70h12M110 82h12M140 62h11M140 74h11" stroke="currentColor" stroke-width="1"/>
                        <path d="M178 108c6-22 14-36 27-46 5 11 8 27 10 46" stroke="currentColor" stroke-width="1.2"/>
                        <path d="M192 74c5-8 11-14 18-18" stroke="currentColor" stroke-width="1"/>
                        <path d="M10 108h214" stroke="currentColor" stroke-width="1.2"/>
                    </svg>
                </div>
            </div>

            <form
                class="pg-careers-form"
                method="post"
                action="<?= h(webPath('actions/career_submit.php')) ?>"
                enctype="multipart/form-data"
                data-careers-form
                novalidate
            >
                <?php if ($status === 'success'): ?>
                    <p class="pg-careers-alert is-ok" role="status" data-careers-status>Application received. We’ll be in touch if there’s a fit.</p>
                <?php elseif ($status === 'duplicate'): ?>
                    <p class="pg-careers-alert is-err" role="alert" data-careers-status>We already have a recent application from you for this role. Please wait before applying again.</p>
                <?php elseif ($status === 'error'): ?>
                    <p class="pg-careers-alert is-err" role="alert" data-careers-status>Please check your details and try again.</p>
                <?php else: ?>
                    <p class="pg-careers-alert is-err" role="alert" data-careers-status hidden></p>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="hp-field" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="pg-careers-form-grid">
                    <div class="field">
                        <label for="name">Full Name <span class="req">*</span></label>
                        <div class="pg-careers-control" data-icon="user">
                            <input id="name" name="name" type="text" maxlength="120" autocomplete="name" placeholder="Your full name" required>
                        </div>
                        <p class="pg-careers-field-error" data-field-error="name" hidden></p>
                    </div>

                    <div class="field">
                        <label for="email">Email Address <span class="req">*</span></label>
                        <div class="pg-careers-control" data-icon="mail">
                            <input id="email" name="email" type="email" maxlength="190" autocomplete="email" placeholder="name@email.com" required>
                        </div>
                        <p class="pg-careers-field-error" data-field-error="email" hidden></p>
                    </div>

                    <div class="field">
                        <label for="phone">Mobile Number <span class="req">*</span></label>
                        <input type="hidden" name="country_code" value="+91">
                        <div class="pg-careers-control" data-icon="phone">
                            <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="15" pattern="\d{8,15}" placeholder="9876543210" autocomplete="tel-national" required>
                        </div>
                        <p class="pg-careers-field-error" data-field-error="phone" hidden></p>
                    </div>

                    <div class="field">
                        <label for="role">Role Applying For <span class="req">*</span></label>
                        <div class="pg-careers-control" data-icon="role">
                            <select id="role" name="role" required>
                                <option value="">Select a Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= h($role) ?>"><?= h($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="pg-careers-field-error" data-field-error="role" hidden></p>
                    </div>

                    <div class="field">
                        <label for="experience">Years of Experience</label>
                        <div class="pg-careers-control" data-icon="exp">
                            <select id="experience" name="experience">
                                <option value="">Select Experience</option>
                                <?php foreach ($experienceOptions as $option): ?>
                                    <option value="<?= h($option) ?>"><?= h($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="notice_period">Notice Period</label>
                        <div class="pg-careers-control" data-icon="time">
                            <select id="notice_period" name="notice_period">
                                <option value="">Select Notice Period</option>
                                <?php foreach ($noticeOptions as $option): ?>
                                    <option value="<?= h($option) ?>"><?= h($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field pg-careers-resume-field">
                        <label for="resume">Upload Resume <span class="req">*</span></label>
                        <div class="pg-careers-resume">
                            <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                            <button class="pg-careers-resume-btn" type="button" data-resume-trigger>Choose File</button>
                            <span class="pg-careers-resume-meta">PDF, DOC or DOCX (Max 5 MB)</span>
                            <p class="pg-careers-resume-name" data-resume-name></p>
                        </div>
                        <p class="pg-careers-field-error" data-field-error="resume" hidden></p>
                    </div>

                    <div class="pg-careers-submit-wrap">
                        <button class="pg-careers-submit" type="submit" data-careers-submit>Submit Application <span aria-hidden="true">↗</span></button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
