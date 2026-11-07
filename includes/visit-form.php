<?php
declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/properties.php';

function renderVisitFields(string $prefix, string $intent = 'visit', string $propertyId = ''): void
{
    ?>
    <form class="visit-form" method="post" action="<?= h(webPath('actions/visit_submit.php')) ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="intent" value="<?= h($intent) ?>">
        <div class="hp-field" aria-hidden="true">
            <label for="<?= h($prefix) ?>-website">Website</label>
            <input id="<?= h($prefix) ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>
        <div class="field">
            <label for="<?= h($prefix) ?>-name">Name</label>
            <input id="<?= h($prefix) ?>-name" name="name" type="text" maxlength="100" autocomplete="name" required>
        </div>
        <div class="field country-field">
            <label for="<?= h($prefix) ?>-phone">Contact number</label>
            <div class="phone-row">
                <div class="country-pick">
                    <button class="country-current" type="button" aria-expanded="false">India +91 <span>⌄</span></button>
                    <div class="country-dropdown" hidden>
                        <input id="<?= h($prefix) ?>-country-search" class="country-search" type="text" placeholder="Search country or code" autocomplete="off" aria-label="Search country code">
                        <ul class="country-list">
                            <?php foreach (countryCodes() as [$country, $code]): ?>
                                <li><button type="button" data-code="<?= h($code) ?>"><?= h($country) ?> <?= h($code) ?></button></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <input type="hidden" name="country_code" value="+91" class="country-code">
                </div>
                <input id="<?= h($prefix) ?>-phone" name="phone" type="tel" inputmode="numeric" maxlength="10" pattern="\d{10}" placeholder="9876543210" required>
            </div>
        </div>
        <div class="field">
            <label for="<?= h($prefix) ?>-email">Email id</label>
            <input id="<?= h($prefix) ?>-email" name="email" type="email" maxlength="254" autocomplete="email" required>
        </div>
        <?php if ($intent === 'visit'): ?>
            <div class="field visit-schedule">
                <span class="field-label">Preferred site-visit date and time</span>
                <div class="visit-when">
                    <div>
                        <label for="<?= h($prefix) ?>-visit-date">Date</label>
                        <input id="<?= h($prefix) ?>-visit-date" name="visit_date" type="date" min="<?= h(date('Y-m-d')) ?>" required>
                    </div>
                    <div>
                        <label for="<?= h($prefix) ?>-visit-time">Time</label>
                        <input id="<?= h($prefix) ?>-visit-time" name="visit_time" type="time" required>
                    </div>
                </div>
                <p class="visit-time-help">Choose a future date and time for your visit.</p>
            </div>
        <?php endif; ?>
        <div class="field property-field">
            <label for="<?= h($prefix) ?>-property-search">Property</label>
            <div class="property-pick">
                <input id="<?= h($prefix) ?>-property-search" class="property-search" type="text" placeholder="City, state, initials or property name" autocomplete="off">
                <input type="hidden" name="property_id" value="<?= h($propertyId) ?>" class="property-id">
                <ul class="property-results" hidden></ul>
            </div>
        </div>
        <div class="field ai-message-field">
            <label for="<?= h($prefix) ?>-message">Message</label>
            <div class="ai-message-control">
                <textarea id="<?= h($prefix) ?>-message" name="message" maxlength="5000" required placeholder="Tell us what you would like to see"></textarea>
                <span class="ai-message-icon" aria-hidden="true">↗</span>
            </div>
            <p class="ai-message-help">Press Shift + Enter for a new line.</p>
        </div>
        <p class="form-status" role="status"></p>
        <button class="btn send-btn" type="submit">Send enquiry</button>
    </form>
    <?php
}

function renderVisitModal(): void
{
    ?>
    <div class="visit-modal" id="visitModal" hidden>
        <div class="visit-card" role="dialog" aria-modal="true" aria-labelledby="visitTitle">
            <button class="visit-close" type="button" aria-label="Close">×</button>
            <p class="eyebrow">PropGemini</p>
            <h2 id="visitTitle">Request a site visit</h2>
            <p class="visit-note">Share a few details and we will follow up. This does not reserve a property.</p>
            <?php renderVisitFields('modal'); ?>
        </div>
    </div>
    <div class="brochure-access-modal" id="brochureAccessModal" hidden>
        <div class="brochure-access-card" role="dialog" aria-modal="true" aria-labelledby="brochureAccessTitle">
            <button class="brochure-access-close" type="button" aria-label="Close">×</button>
            <span class="brochure-access-icon" aria-hidden="true">✓</span>
            <p class="eyebrow">Access confirmed</p>
            <h2 id="brochureAccessTitle">Your brochure is ready.</h2>
            <p>Thank you for your enquiry. Download the project brochure whenever you are ready.</p>
            <a class="btn brochure-download-link" href="#" data-brochure-base="<?= h(webPath('actions/brochure.php')) ?>">Download brochure ↧</a>
        </div>
    </div>
    <script type="application/json" id="propertyIndex"><?= json_encode(array_map(static function (array $property): array {
        return [
            'id' => $property['id'],
            'name' => $property['name'],
            'city' => $property['city'],
            'state' => $property['state'],
            'area' => $property['area'],
        ];
    }, properties()), JSON_UNESCAPED_UNICODE) ?></script>
    <?php
}
