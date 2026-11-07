<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';

$pageTitle = 'EMI Calculator | PropGemini';
$pageDescription = 'Estimate your home loan EMI with the PropGemini calculator.';
$bodyClass = 'emi-page';

$emiLoan = (int) ($_GET['loan'] ?? 0);
if ($emiLoan <= 0) {
    $emiLoan = (int) siteSetting('emi_default_loan', '5000000');
}
$emiLoan = max(500000, min(50000000, $emiLoan));
$emiRate = (float) siteSetting('emi_default_rate', '8.5');
$emiRate = max(5, min(15, $emiRate));
$emiYears = (int) siteSetting('emi_default_years', '20');
$emiYears = max(1, min(30, $emiYears));

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>

<style>
/* ==========================================
   PROPGEMINI EMI CALCULATOR
========================================== */

.pg-emi {
    --emi-gold: #c4a46d;
    --emi-gold-light: #e7d7b4;
    --emi-ink: #141311;
    --emi-muted: #6e675e;

    background: #e8e2d6;
    color: var(--emi-ink);
    overflow: hidden;
}

/* PAGE INTRO */

.pg-emi-intro {
    position: relative;
    padding: 65px 0 38px;
}

.pg-emi-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #a7834d;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .2em;
    text-transform: uppercase;
}

.pg-emi-eyebrow::before {
    content: "";
    width: 28px;
    height: 1px;
    background: currentColor;
}

.pg-emi-intro h1 {
    margin: 17px 0 12px;
    font-family: var(--serif, Georgia, serif);
    font-size: clamp(42px, 5vw, 66px);
    font-weight: 400;
    letter-spacing: -1.5px;
    line-height: 1.04;
}

.pg-emi-intro h1 em {
    color: #b38c55;
    font-weight: 400;
}

.pg-emi-intro p {
    max-width: 540px;
    margin: 0;
    color: #777168;
    font-size: 14px;
    line-height: 1.85;
}

/* CALCULATOR LAYOUT */

.pg-emi-layout {
    display: grid;
    grid-template-columns: 1.08fr .92fr;
    gap: 22px;
    align-items: stretch;
    padding-bottom: 65px;
}

/* INPUT PANEL */

.pg-emi-panel {
    position: relative;
    overflow: hidden;
    padding: 34px;
    border: 1px solid #e5ddd0;
    border-radius: 22px;
    background: rgba(255,255,255,.76);
    box-shadow: 0 12px 45px rgba(32,26,17,.035);
}

.pg-emi-panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 32px;
}

.pg-emi-panel-head h2 {
    margin: 0;
    font-family: var(--serif, Georgia, serif);
    font-size: 30px;
    font-weight: 400;
}

.pg-emi-panel-head span {
    color: #a7834d;
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
}

/* FIELD */

.pg-emi-field {
    margin-bottom: 28px;
}

.pg-emi-field:last-child {
    margin-bottom: 0;
}

.pg-emi-field-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 14px;
}

.pg-emi-field-label label {
    color: #777168;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.pg-emi-number {
    width: 145px;
    padding: 8px 12px;
    border: 1px solid #e6ddcf;
    border-radius: 10px;
    background: #fffdf9;
    color: #1b1a18;
    font-size: 15px;
    font-weight: 600;
    text-align: right;
    transition: border-color .25s, box-shadow .25s;
}

.pg-emi-number:focus {
    outline: none;
    border-color: var(--emi-gold);
    box-shadow: 0 0 0 3px rgba(201,166,107,.12);
}

/* RANGE */

.pg-emi-range {
    width: 100%;
    height: 5px;
    margin: 8px 0;
    border-radius: 20px;
    appearance: none;
    -webkit-appearance: none;
    background: linear-gradient(
        90deg,
        #c9a66b 0%,
        #c9a66b 35%,
        #e8e0d3 35%,
        #e8e0d3 100%
    );
    cursor: pointer;
    outline: none;
}

.pg-emi-range::-webkit-slider-thumb {
    appearance: none;
    -webkit-appearance: none;
    width: 17px;
    height: 17px;
    border: 3px solid #fff;
    border-radius: 50%;
    background: #c9a66b;
    box-shadow: 0 0 0 1px #c9a66b, 0 3px 10px #0000001c;
    transition: transform .2s;
}

.pg-emi-range::-moz-range-thumb {
    width: 13px;
    height: 13px;
    border: 3px solid #fff;
    border-radius: 50%;
    background: #c9a66b;
    box-shadow: 0 0 0 1px #c9a66b;
}

.pg-emi-range:hover::-webkit-slider-thumb {
    transform: scale(1.2);
}

.pg-emi-range-labels {
    display: flex;
    justify-content: space-between;
    color: #a19a8e;
    font-size: 10px;
}

/* RESULTS PANEL */

.pg-emi-results {
    position: relative;
    isolation: isolate;
    overflow: hidden;

    display: flex;
    flex-direction: column;
    justify-content: center;

    min-height: 420px;
    padding: 36px;

    border-radius: 22px;
    background: #191918;
    color: #fff;

    box-shadow: 0 18px 50px rgba(21,19,15,.12);
}

.pg-emi-results::before {
    content: "";
    position: absolute;
    z-index: -1;
    top: -170px;
    right: -140px;
    width: 420px;
    height: 420px;
    border: 1px solid rgba(201,166,107,.18);
    border-radius: 50%;
    animation: emiOrbit 30s linear infinite;
}

.pg-emi-results::after {
    content: "";
    position: absolute;
    z-index: -1;
    top: -110px;
    right: -80px;
    width: 300px;
    height: 300px;
    border: 1px solid rgba(201,166,107,.10);
    border-radius: 50%;
}

.pg-emi-result-eyebrow {
    color: #d5b77f;
    font-size: 11px;
    letter-spacing: .19em;
    text-transform: uppercase;
}

.pg-emi-result-label {
    margin: 12px 0 4px;
    color: #c5bfb4;
    font-size: 12px;
}

.pg-emi-monthly {
    display: flex;
    align-items: baseline;
    gap: 5px;
    font-family: var(--serif, Georgia, serif);
    font-size: clamp(42px, 4.5vw, 64px);
    line-height: 1.12;
    letter-spacing: -1.8px;
    color: #f8f2e7;
    font-weight: 400;
}

.pg-emi-monthly .emi-currency {
    font-size: .58em;
    color: #d4b57c;
}

.pg-emi-caption {
    margin-top: 9px;
    color: #9e988d;
    font-size: 11px;
}

/* BREAKDOWN */

.pg-emi-breakdown {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;

    margin-top: 26px;
    padding-top: 22px;

    border-top: 1px solid rgba(255,255,255,.12);
}

.pg-emi-breakdown span {
    display: block;
    color: #a9a298;
    font-size: 10px;
    letter-spacing: .11em;
    text-transform: uppercase;
}

.pg-emi-breakdown strong {
    display: block;
    margin-top: 8px;
    font-family: var(--serif, Georgia, serif);
    font-size: 25px;
    font-weight: 400;
    color: #f7f1e7;
}

/* DONUT CHART */

.pg-emi-chart-row {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-top: 28px;
}

.pg-emi-donut {
    position: relative;
    flex: 0 0 105px;
    width: 105px;
    height: 105px;
    border-radius: 50%;

    background: conic-gradient(
        #d0ac70 0% 50%,
        #514a3d 50% 100%
    );

    transform: rotate(-90deg);
    transition: background 1s cubic-bezier(.2,.7,.2,1);
}

.pg-emi-donut::before {
    content: "";
    position: absolute;
    inset: 13px;
    border-radius: 50%;
    background: #191918;
}

.pg-emi-donut::after {
    content: "₹";
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    color: #d6b780;
    font-size: 23px;
    transform: rotate(90deg);
}

.pg-emi-chart-legend {
    display: grid;
    gap: 12px;
}

.pg-emi-legend-item {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #bcb5a9;
    font-size: 11px;
}

.pg-emi-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #d0ac70;
}

.pg-emi-legend-dot.interest {
    background: #514a3d;
    border: 1px solid #94856b;
}

/* CTA */

.pg-emi-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 15px;

    align-self: flex-start;
    margin-top: 28px;

    padding: 13px 24px;
    border: 1px solid #d0ac70;
    border-radius: 999px;

    background: #d0ac70;
    color: #191918;

    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    text-decoration: none;

    transition:
        background .3s ease,
        color .3s ease,
        transform .3s ease,
        box-shadow .3s ease;
}

.pg-emi-cta:hover {
    background: transparent;
    color: #e6c991;
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(201,166,107,.13);
}

.pg-emi-cta span {
    font-size: 17px;
    transition: transform .3s ease;
}

.pg-emi-cta:hover span {
    transform: translateX(4px);
}

/* NOTE */

.pg-emi-note {
    max-width: 800px;
    margin: 0 0 55px;
    color: #827a6f;
    font-size: 11px;
    line-height: 1.8;
}

/* REVEAL */

.pg-emi-reveal {
    opacity: 0;
    transform: translateY(25px);
    transition:
        opacity .8s ease,
        transform .8s cubic-bezier(.2,.7,.2,1);
}

.pg-emi-reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ANIMATIONS */

@keyframes emiOrbit {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* RESPONSIVE */

@media (max-width: 900px) {
    .pg-emi-layout {
        grid-template-columns: 1fr;
    }

    .pg-emi-results {
        min-height: auto;
    }

    .pg-emi-monthly {
        font-size: clamp(46px, 8vw, 64px);
    }
}

@media (max-width: 600px) {
    .pg-emi-intro {
        padding: 45px 0 28px;
    }

    .pg-emi-intro h1 {
        font-size: 43px;
    }

    .pg-emi-layout {
        gap: 16px;
        padding-bottom: 40px;
    }

    .pg-emi-panel,
    .pg-emi-results {
        padding: 25px 22px;
        border-radius: 18px;
    }

    .pg-emi-panel-head h2 {
        font-size: 27px;
    }

    .pg-emi-field-label {
        align-items: flex-start;
        flex-direction: column;
        gap: 9px;
    }

    .pg-emi-number {
        width: 100%;
        text-align: left;
    }

    .pg-emi-monthly {
        font-size: 45px;
    }

    .pg-emi-chart-row {
        gap: 18px;
    }

    .pg-emi-donut {
        width: 90px;
        height: 90px;
        flex-basis: 90px;
    }

    .pg-emi-breakdown {
        gap: 12px;
    }

    .pg-emi-breakdown strong {
        font-size: 22px;
    }
}

/* ACCESSIBILITY */

@media (prefers-reduced-motion: reduce) {
    .pg-emi-results::before {
        animation: none;
    }

    .pg-emi-reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .pg-emi-cta,
    .pg-emi-donut {
        transition: none;
    }
}
</style>

<main class="pg-emi">
    <?php renderPublicPageHero(
        'EMI Calculator',
        'Plan your next move.',
        'Adjust the loan, rate and tenure to see an estimated monthly commitment before you decide.',
        'Know your numbers.'
    ); ?>

    <!-- CALCULATOR -->
    <section class="wrap">

        <div class="pg-emi-layout">

            <!-- INPUTS -->
            <form class="pg-emi-panel pg-emi-reveal" id="emiCalculator">

                <div class="pg-emi-panel-head">
                    <h2>Loan details</h2>
                    <span>Adjust to explore</span>
                </div>

                <!-- LOAN AMOUNT -->
                <div class="pg-emi-field">

                    <div class="pg-emi-field-label">
                        <label for="loanAmountInput">
                            Loan amount
                        </label>

                        <input
                            class="pg-emi-number"
                            id="loanAmountInput"
                            type="number"
                            min="500000"
                            max="50000000"
                            step="100000"
                            value="<?= (int) $emiLoan ?>"
                            inputmode="numeric"
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

                    <div class="pg-emi-range-labels">
                        <span>₹5 Lakh</span>
                        <span>₹5 Crore</span>
                    </div>

                </div>

                <!-- INTEREST RATE -->
                <div class="pg-emi-field">

                    <div class="pg-emi-field-label">
                        <label for="interestRateInput">
                            Interest rate
                        </label>

                        <input
                            class="pg-emi-number"
                            id="interestRateInput"
                            type="number"
                            min="5"
                            max="15"
                            step="0.05"
                            value="<?= h(rtrim(rtrim(number_format($emiRate, 2, '.', ''), '0'), '.')) ?>"
                            inputmode="decimal"
                        >
                    </div>

                    <input
                        class="pg-emi-range"
                        id="interestRate"
                        type="range"
                        min="5"
                        max="15"
                        step="0.05"
                        value="<?= h(rtrim(rtrim(number_format($emiRate, 2, '.', ''), '0'), '.')) ?>"
                        aria-label="Annual interest rate"
                    >

                    <div class="pg-emi-range-labels">
                        <span>5%</span>
                        <span>15%</span>
                    </div>

                </div>

                <!-- TENURE -->
                <div class="pg-emi-field">

                    <div class="pg-emi-field-label">
                        <label for="tenureYearsInput">
                            Loan tenure
                        </label>

                        <input
                            class="pg-emi-number"
                            id="tenureYearsInput"
                            type="number"
                            min="1"
                            max="30"
                            step="1"
                            value="<?= (int) $emiYears ?>"
                            inputmode="numeric"
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
                        <span>1 year</span>
                        <span>30 years</span>
                    </div>

                </div>

            </form>

            <!-- RESULTS -->
            <div class="pg-emi-results pg-emi-reveal" aria-live="polite">

                <div class="pg-emi-result-eyebrow">
                    Your estimated monthly EMI
                </div>

                <div class="pg-emi-result-label">
                    Monthly instalment
                </div>

                <div class="pg-emi-monthly">
                    <span class="emi-currency">₹</span>
                    <span id="monthlyEmi">0</span>
                </div>

                <div class="pg-emi-caption">
                    Based on your selected loan details
                </div>

                <!-- BREAKDOWN -->
                <div class="pg-emi-breakdown">

                    <div>
                        <span>Total interest</span>
                        <strong id="totalInterest">₹0</strong>
                    </div>

                    <div>
                        <span>Total payable</span>
                        <strong id="totalPayable">₹0</strong>
                    </div>

                </div>

                <!-- DONUT CHART -->
                <div class="pg-emi-chart-row">

                    <div
                        class="pg-emi-donut"
                        id="emiDonut"
                        role="img"
                        aria-label="Loan principal and interest breakdown"
                    ></div>

                    <div class="pg-emi-chart-legend">

                        <div class="pg-emi-legend-item">
                            <span class="pg-emi-legend-dot"></span>
                            <span>Principal amount</span>
                        </div>

                        <div class="pg-emi-legend-item">
                            <span class="pg-emi-legend-dot interest"></span>
                            <span>Interest amount</span>
                        </div>

                    </div>

                </div>

                <a
                    class="pg-emi-cta"
                    href="<?= h(webPath('pages/enquire.php')) ?>"
                >
                    Enquire Now
                    <span>↗</span>
                </a>

            </div>

        </div>

        <p class="pg-emi-note">
            EMI is calculated using the standard reducing-balance
            formula. This calculator provides an estimate only.
            Actual loan terms, interest rates, fees and repayment
            schedules may vary by lender. This is not a loan offer.
        </p>

    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('emiCalculator');

    if (!form) return;

    const loanRange = document.getElementById('loanAmount');
    const loanInput = document.getElementById('loanAmountInput');

    const rateRange = document.getElementById('interestRate');
    const rateInput = document.getElementById('interestRateInput');

    const tenureRange = document.getElementById('tenureYears');
    const tenureInput = document.getElementById('tenureYearsInput');

    const emiOutput = document.getElementById('monthlyEmi');
    const interestOutput = document.getElementById('totalInterest');
    const payableOutput = document.getElementById('totalPayable');
    const donut = document.getElementById('emiDonut');

    const money = value => Math.round(value).toLocaleString('en-IN');

    /* =====================================
       RANGE BACKGROUND
    ===================================== */

    function updateRangeBackground(range) {

        const min = Number(range.min);
        const max = Number(range.max);
        const value = Number(range.value);

        const percent = ((value - min) / (max - min)) * 100;

        range.style.background =
            'linear-gradient(90deg, ' +
            '#c9a66b 0%, ' +
            '#c9a66b ' + percent + '%, ' +
            '#e8e0d3 ' + percent + '%, ' +
            '#e8e0d3 100%)';
    }

    /* =====================================
       EMI CALCULATION
    ===================================== */

    function calculateEMI() {

        const principal = Number(loanRange.value);
        const annualRate = Number(rateRange.value);
        const years = Number(tenureRange.value);

        const months = years * 12;
        const monthlyRate = annualRate / 12 / 100;

        let emi;

        if (monthlyRate === 0) {
            emi = principal / months;
        } else {
            const growth = Math.pow(1 + monthlyRate, months);

            emi =
                principal *
                monthlyRate *
                growth /
                (growth - 1);
        }

        const totalPayable = emi * months;
        const totalInterest = totalPayable - principal;

        /* Update result values */
        emiOutput.textContent = money(emi);
        interestOutput.textContent = '₹' + money(totalInterest);
        payableOutput.textContent = '₹' + money(totalPayable);

        /* Update donut visualization */
        const principalPercent =
            (principal / totalPayable) * 100;

        const interestPercent =
            100 - principalPercent;

        donut.style.background =
            'conic-gradient(' +
            '#d0ac70 0% ' + principalPercent + '%, ' +
            '#514a3d ' + principalPercent + '% 100%)';

        donut.setAttribute(
            'aria-label',
            'Principal ' +
            principalPercent.toFixed(1) +
            ' percent, interest ' +
            interestPercent.toFixed(1) +
            ' percent'
        );

        /* Update slider backgrounds */
        updateRangeBackground(loanRange);
        updateRangeBackground(rateRange);
        updateRangeBackground(tenureRange);
    }

    /* =====================================
       INPUT SYNC
    ===================================== */

    function syncInput(range, input) {

        range.addEventListener('input', function () {

            input.value = range.value;
            calculateEMI();

        });

        input.addEventListener('input', function () {

            const value = Number(input.value);

            if (!Number.isFinite(value)) return;

            if (
                value >= Number(range.min) &&
                value <= Number(range.max)
            ) {

                range.value = String(value);
                calculateEMI();

            }

        });

        input.addEventListener('change', function () {

            const value = Number(input.value);

            const clamped = Math.min(
                Number(range.max),
                Math.max(Number(range.min), value)
            );

            range.value = String(clamped);
            input.value = String(clamped);

            calculateEMI();

        });
    }

    syncInput(loanRange, loanInput);
    syncInput(rateRange, rateInput);
    syncInput(tenureRange, tenureInput);

    /* =====================================
       URL PARAMETER SUPPORT
    ===================================== */

    const params = new URLSearchParams(window.location.search);

    function setValue(range, input, param) {

        if (!params.has(param)) return;

        const value = Number(params.get(param));

        if (!Number.isFinite(value)) return;

        const clamped = Math.min(
            Number(range.max),
            Math.max(Number(range.min), value)
        );

        range.value = String(clamped);
        input.value = String(clamped);
    }

    setValue(loanRange, loanInput, 'loan');
    setValue(rateRange, rateInput, 'rate');
    setValue(tenureRange, tenureInput, 'years');

    /* =====================================
       SCROLL REVEAL
    ===================================== */

    const revealElements = document.querySelectorAll(
        '.pg-emi-reveal'
    );

    if ('IntersectionObserver' in window) {

        const revealObserver = new IntersectionObserver(
            function (entries) {

                entries.forEach(function (entry) {

                    if (entry.isIntersecting) {

                        entry.target.classList.add('is-visible');

                        revealObserver.unobserve(entry.target);

                    }

                });

            },
            {
                threshold: 0.12
            }
        );

        revealElements.forEach(function (element) {
            revealObserver.observe(element);
        });

    } else {

        revealElements.forEach(function (element) {
            element.classList.add('is-visible');
        });

    }

    /* Prevent form submission */
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    /* Initial calculation */
    calculateEMI();

});
</script>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
