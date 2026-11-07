<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/social.php';

$pageTitle = 'Careers at PropGemini';
$pageDescription = 'Join PropGemini. Open roles in property advisory, client relations and site visits across Pune, Mumbai and Gurgaon.';
$bodyClass = 'careers-page';
$status = (string) ($_GET['status'] ?? '');
$roles = careerRoles();

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main>


    <section class="pg-section">
        <div class="wrap">
            <p class="pg-kicker">Why join</p>
            <h2>A quieter kind of workplace.</h2>
            <p class="pg-lead">Training, real client conversations and room to grow across three cities.</p>
            <div class="pg-grid">
                <article class="pg-card"><div class="num">01</div><h3>Real properties</h3><p>Work with published listings, site visits and buyers who are ready to look.</p></article>
                <article class="pg-card"><div class="num">02</div><h3>Clear training</h3><p>Learn how we present a home, handle an enquiry and prepare a visit.</p></article>
                <article class="pg-card"><div class="num">03</div><h3>Three cities</h3><p>Pune, Mumbai and Gurgaon — local knowledge with one advisory standard.</p></article>
                <article class="pg-card"><div class="num">04</div><h3>Room to grow</h3><p>Advisors who look after clients well take on more of the desk.</p></article>
            </div>
        </div>
    </section>

    <section class="pg-section career-perks">
        <div class="wrap">
            <h2>Perks &amp; benefits</h2>
            <div class="perk-grid">
                <article class="perk">
                    <span class="perk-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 18V8m0 0h3.2a2.4 2.4 0 0 1 0 4.8H6"/></svg></span>
                    <h3>Competitive compensation</h3>
                </article>
                <article class="perk">
                    <span class="perk-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 20s-6-3.6-6-8.2A3.4 3.4 0 0 1 12 9a3.4 3.4 0 0 1 6 2.8C18 16.4 12 20 12 20z"/><path d="M8.5 12.5h2.2l.8-1.6 1.4 3.2 1-1.6h1.6"/></svg></span>
                    <h3>Healthcare &amp; insurance</h3>
                </article>
                <article class="perk">
                    <span class="perk-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 2.1 4.6L19 8.2l-3.5 3.3.9 4.8L12 14.8 7.6 16.3l.9-4.8L5 8.2l4.9-.6z"/></svg></span>
                    <h3>Best incentive structure</h3>
                </article>
                <article class="perk">
                    <span class="perk-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 7h14M5 12h14M5 17h9"/></svg></span>
                    <h3>Performance appraisals</h3>
                </article>
                <article class="perk">
                    <span class="perk-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 18V6m0 12h16"/><path d="m7 14 3.2-3.2 2.4 2.2L18 7"/></svg></span>
                    <h3>Career growth and opportunities</h3>
                </article>
                <article class="perk">
                    <span class="perk-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="7"/><path d="M9 13.2c.6.8 1.5 1.3 3 1.3s2.4-.5 3-1.3M9 10h.01M15 10h.01"/></svg></span>
                    <h3>Fun and lively work environment</h3>
                </article>
            </div>
        </div>
    </section>

    <section class="pg-section" id="apply" style="padding-top:0;">
        <div class="wrap pg-apply">
            <div>
                <p class="pg-kicker">Apply</p>
                <h2>Tell us about yourself.</h2>
                <p class="pg-lead">Share a short note and upload your résumé. We reply to relevant applications.</p>
            </div>
            <form class="pg-form" action="<?= h(webPath('actions/career_submit.php')) ?>" method="post" enctype="multipart/form-data">
                <?php if ($status === 'success'): ?>
                    <p class="pg-note" role="status">Application received. We will be in touch if there is a fit.</p>
                <?php elseif ($status === 'error'): ?>
                    <p class="pg-note error" role="alert">Please check your details and try again.</p>
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="hp-field" aria-hidden="true">
                    <label for="website">Website</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Full name</label>
                        <input id="name" name="name" required maxlength="100" autocomplete="name">
                    </div>
                    <div class="field">
                        <label for="phone">Mobile</label>
                        <input id="phone" name="phone" required inputmode="numeric" maxlength="10" pattern="\d{10}" autocomplete="tel">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required maxlength="254" autocomplete="email">
                    </div>
                    <div class="field">
                        <label for="experience">Years of experience</label>
                        <select id="experience" name="experience" required>
                            <option value="">Select</option>
                            <option value="0-2">0–2</option>
                            <option value="2-4">2–4</option>
                            <option value="4-6">4–6</option>
                            <option value="6+">6+</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= h($role['title']) ?>"><?= h($role['title']) ?></option>
                            <?php endforeach; ?>
                            <option value="General application">General application</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label for="resume">Upload résumé</label>
                        <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    </div>
                    <div class="field full">
                        <label for="message">Note</label>
                        <textarea id="message" name="message" maxlength="5000" required placeholder="A few lines on your experience and the city you can work in."></textarea>
                    </div>
                </div>
                <button class="btn" type="submit">Submit application</button>
            </form>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
