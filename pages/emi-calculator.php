<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/visit-form.php';

$pageTitle = 'EMI Calculator | PropGemini';
$pageDescription = 'Estimate your home loan EMI with the PropGemini calculator.';
$bodyClass = 'emi-page';
$pageStyles = ['public/css/emi.css'];
$pageScripts = ['public/js/emi.js'];

$emiLoan = (int) ($_GET['loan'] ?? 0);
if ($emiLoan <= 0) {
    $emiLoan = (int) siteSetting('emi_default_loan', '5000000');
}
$emiLoan = max(500000, min(50000000, $emiLoan));
$emiRate = (float) siteSetting('emi_default_rate', '8.5');
$emiRate = max(5, min(15, $emiRate));
$emiYears = (int) siteSetting('emi_default_years', '20');
$emiYears = max(1, min(30, $emiYears));
$rateDisplay = rtrim(rtrim(number_format($emiRate, 2, '.', ''), '0'), '.');
$activeTab = (($_GET['tab'] ?? '') === 'enquire') ? 'enquire' : 'emi';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="pg-emi">
    <section class="pg-emi-hero" aria-labelledby="pg-emi-heading">
        <div class="pg-emi-visual" aria-hidden="true">
            <img class="pg-emi-visual-img" src="<?= h(webPath('public/images/hero.jpg')) ?>" alt="" width="1920" height="1080" decoding="async">
            <div class="pg-emi-visual-shade"></div>
        </div>

        <div class="wrap pg-emi-shell">
            <div class="pg-emi-copy pg-emi-reveal" data-emi-reveal>
                <p class="pg-emi-kicker">Enquire &amp; plan</p>
                <h1 id="pg-emi-heading">Your home journey made simpler.</h1>
                <ul class="pg-emi-benefits">
                    <li>Calculate EMI instantly</li>
                    <li>Get a personalised quote</li>
                    <li>Speak with our property experts</li>
                    <li>Find the right home for your goals</li>
                </ul>
            </div>

            <div class="pg-emi-card pg-emi-reveal" data-emi-reveal data-emi-delay="1">
                <div class="pg-emi-tabs" role="tablist" aria-label="EMI tools">
                    <button
                        class="pg-emi-tab<?= $activeTab === 'emi' ? ' is-active' : '' ?>"
                        type="button"
                        role="tab"
                        id="pgEmiTabCalc"
                        aria-controls="pgEmiPanelCalc"
                        aria-selected="<?= $activeTab === 'emi' ? 'true' : 'false' ?>"
                        data-emi-tab="emi"
                    >
                        <i class="fa-solid fa-calculator" aria-hidden="true"></i>
                        <span>EMI Calculator</span>
                    </button>
                    <button
                        class="pg-emi-tab<?= $activeTab === 'enquire' ? ' is-active' : '' ?>"
                        type="button"
                        role="tab"
                        id="pgEmiTabEnquire"
                        aria-controls="pgEmiPanelEnquire"
                        aria-selected="<?= $activeTab === 'enquire' ? 'true' : 'false' ?>"
                        data-emi-tab="enquire"
                    >
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <span>Enquire Now</span>
                    </button>
                </div>

                <div
                    class="pg-emi-panel<?= $activeTab === 'emi' ? ' is-active' : '' ?>"
                    id="pgEmiPanelCalc"
                    role="tabpanel"
                    aria-labelledby="pgEmiTabCalc"
                    <?= $activeTab === 'emi' ? '' : 'hidden' ?>
                >
                    <form class="pg-emi-calc" id="emiCalculator" novalidate>
                        <div class="pg-emi-calc-grid">
                            <div class="pg-emi-fields">
                                <div class="pg-emi-field">
                                    <div class="pg-emi-field-head">
                                        <label for="loanAmountInput">Loan Amount</label>
                                        <input
                                            class="pg-emi-number"
                                            id="loanAmountInput"
                                            type="text"
                                            inputmode="numeric"
                                            value="₹ <?= h(number_format($emiLoan, 0, '.', ',')) ?>"
                                            data-raw="<?= (int) $emiLoan ?>"
                                            aria-describedby="loanAmountHint"
                                        >
                                    </div>
                                    <input
                                        class="pg-emi-range"
                                        id="loanAmount"
                                        type="range"
                                        min="500000"
                                        max="50000000"
                                        step="100000"
                                        value="<?= (int) $emiLoan ?>"
                                        aria-label="Loan amount in rupees"
                                    >
                                    <div class="pg-emi-range-labels" id="loanAmountHint">
                                        <span>₹ 5 Lakh</span>
                                        <span>₹ 5 Crore</span>
                                    </div>
                                </div>

                                <div class="pg-emi-field">
                                    <div class="pg-emi-field-head">
                                        <label for="interestRateInput">Interest Rate</label>
                                        <input
                                            class="pg-emi-number"
                                            id="interestRateInput"
                                            type="text"
                                            inputmode="decimal"
                                            value="<?= h($rateDisplay) ?> %"
                                            data-raw="<?= h($rateDisplay) ?>"
                                        >
                                    </div>
                                    <input
                                        class="pg-emi-range"
                                        id="interestRate"
                                        type="range"
                                        min="5"
                                        max="15"
                                        step="0.05"
                                        value="<?= h($rateDisplay) ?>"
                                        aria-label="Annual interest rate"
                                    >
                                    <div class="pg-emi-range-labels">
                                        <span>5%</span>
                                        <span>15%</span>
                                    </div>
                                </div>

                                <div class="pg-emi-field">
                                    <div class="pg-emi-field-head">
                                        <label for="tenureYearsInput">Loan Tenure</label>
                                        <input
                                            class="pg-emi-number"
                                            id="tenureYearsInput"
                                            type="text"
                                            inputmode="numeric"
                                            value="<?= (int) $emiYears ?> Years"
                                            data-raw="<?= (int) $emiYears ?>"
                                        >
                                    </div>
                                    <input
                                        class="pg-emi-range"
                                        id="tenureYears"
                                        type="range"
                                        min="1"
                                        max="30"
                                        step="1"
                                        value="<?= (int) $emiYears ?>"
                                        aria-label="Loan tenure in years"
                                    >
                                    <div class="pg-emi-range-labels">
                                        <span>1 Year</span>
                                        <span>30 Years</span>
                                    </div>
                                </div>
                            </div>

                            <aside class="pg-emi-results" aria-live="polite">
                                <p class="pg-emi-result-label">Monthly EMI</p>
                                <p class="pg-emi-monthly"><span id="monthlyEmi">₹ 0</span></p>

                                <div class="pg-emi-breakdown">
                                    <div class="pg-emi-break-row">
                                        <span class="pg-emi-break-icon" aria-hidden="true"><i class="fa-solid fa-coins"></i></span>
                                        <div>
                                            <span>Total Interest</span>
                                            <strong id="totalInterest">₹ 0</strong>
                                        </div>
                                    </div>
                                    <div class="pg-emi-break-row">
                                        <span class="pg-emi-break-icon" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
                                        <div>
                                            <span>Total Payable</span>
                                            <strong id="totalPayable">₹ 0</strong>
                                        </div>
                                    </div>
                                </div>

                                <button class="pg-emi-cta" type="button" data-emi-tab="enquire">
                                    Enquire Now <span aria-hidden="true">↗</span>
                                </button>
                            </aside>
                        </div>
                    </form>
                </div>

                <div
                    class="pg-emi-panel pg-emi-panel-enquire<?= $activeTab === 'enquire' ? ' is-active' : '' ?>"
                    id="pgEmiPanelEnquire"
                    role="tabpanel"
                    aria-labelledby="pgEmiTabEnquire"
                    <?= $activeTab === 'enquire' ? '' : 'hidden' ?>
                >
                    <form class="visit-form pg-emi-enquire" method="post" action="<?= h(webPath('actions/visit_submit.php')) ?>" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="intent" value="enquire">
                        <div class="hp-field" aria-hidden="true">
                            <label for="emi-website">Website</label>
                            <input id="emi-website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="pg-emi-enquire-grid">
                            <div class="field">
                                <label for="emi-name">Full Name *</label>
                                <input id="emi-name" name="name" type="text" maxlength="100" autocomplete="name" required placeholder="Your full name">
                            </div>

                            <div class="field phone-field">
                                <label for="emi-phone">Mobile Number *</label>
                                <input type="hidden" name="country_code" value="+91">
                                <input id="emi-phone" name="phone" type="tel" inputmode="numeric" maxlength="10" pattern="\d{10}" placeholder="9876543210" required>
                            </div>

                            <div class="field">
                                <label for="emi-email">Email Address *</label>
                                <input id="emi-email" name="email" type="email" maxlength="254" autocomplete="email" required placeholder="name@email.com">
                            </div>

                            <div class="field">
                                <label for="emi-city">Preferred City *</label>
                                <select id="emi-city" name="preferred_city" required>
                                    <option value="">Select city</option>
                                    <option value="Pune">Pune</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Gurgaon">Gurgaon</option>
                                </select>
                            </div>

                            <div class="field property-field">
                                <label for="emi-property-search">Interested Property</label>
                                <div class="property-pick">
                                    <input id="emi-property-search" class="property-search" type="text" placeholder="Select a preferred city first" data-open-placeholder="Search by name or area" autocomplete="off" disabled>
                                    <input type="hidden" name="property_id" value="" class="property-id">
                                    <ul class="property-results" hidden></ul>
                                </div>
                            </div>

                            <div class="field">
                                <label for="emi-type">Property Type</label>
                                <select id="emi-type" name="property_type">
                                    <option value="">Select type</option>
                                    <option value="Apartment">Apartment</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Penthouse">Penthouse</option>
                                    <option value="Plot">Plot</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>

                            <div class="field">
                                <label for="emi-budget">Budget Range</label>
                                <select id="emi-budget" name="budget_range">
                                    <option value="">Select budget</option>
                                    <option value="Under ₹1 Cr">Under ₹1 Cr</option>
                                    <option value="₹1–2 Cr">₹1–2 Cr</option>
                                    <option value="₹2–5 Cr">₹2–5 Cr</option>
                                    <option value="₹5 Cr+">₹5 Cr+</option>
                                </select>
                            </div>

                            <div class="field ai-message-field pg-emi-enquire-full">
                                <label for="emi-message">Message *</label>
                                <textarea id="emi-message" name="message" maxlength="5000" required placeholder="Share your requirements, timeline or preferred configuration"></textarea>
                            </div>
                        </div>

                        <label class="pg-emi-consent">
                            <input type="checkbox" name="whatsapp_consent" value="1">
                            <span>I agree to receive WhatsApp follow-up messages about my enquiry.</span>
                        </label>

                        <p class="form-status" role="status"></p>
                        <button class="pg-emi-submit" type="submit">Submit Enquiry <span aria-hidden="true">↗</span></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="wrap">
            <ul class="pg-emi-strip" aria-label="Why plan with PropGemini">
                <li class="pg-emi-strip-item" data-emi-reveal data-emi-delay="0">
                    <span class="pg-emi-strip-icon" aria-hidden="true"><i class="fa-solid fa-coins"></i></span>
                    <div>
                        <strong>EMI Planning</strong>
                        <span>Plan your monthly budget.</span>
                    </div>
                </li>
                <li class="pg-emi-strip-item" data-emi-reveal data-emi-delay="1">
                    <span class="pg-emi-strip-icon" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
                    <div>
                        <strong>Expert Guidance</strong>
                        <span>Get personalised advice.</span>
                    </div>
                </li>
                <li class="pg-emi-strip-item" data-emi-reveal data-emi-delay="2">
                    <span class="pg-emi-strip-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                    <div>
                        <strong>Verified Properties</strong>
                        <span>RERA-compliant &amp; trusted.</span>
                    </div>
                </li>
                <li class="pg-emi-strip-item" data-emi-reveal data-emi-delay="3">
                    <span class="pg-emi-strip-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <div>
                        <strong>End-to-End Support</strong>
                        <span>From enquiry to possession.</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
