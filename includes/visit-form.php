<?php
declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/properties.php';

/**
 * @param array{
 *   form_class?: string,
 *   labels?: array<string, string>,
 *   placeholders?: array<string, string>,
 *   country_label?: string,
 *   button?: string,
 *   required_marks?: bool,
 *   show_city?: bool,
 *   cities?: list<string>,
 *   hide_property?: bool,
 *   locked_property?: bool
 * } $ui
 */
function renderVisitFields(string $prefix, string $intent = 'visit', string $propertyId = '', array $ui = []): void
{
    $labels = array_merge([
        'name' => 'Name',
        'phone' => 'Contact number',
        'email' => 'Email id',
        'city' => 'City',
        'property' => 'Property',
        'message' => 'Message',
    ], $ui['labels'] ?? []);

    $placeholders = array_merge([
        'name' => '',
        'phone' => '9876543210',
        'email' => '',
        'property' => 'City, state, initials or property name',
        'message' => 'Tell us what you would like to see',
    ], $ui['placeholders'] ?? []);

    $formClass = trim('visit-form ' . (string) ($ui['form_class'] ?? ''));
    $buttonHtml = (string) ($ui['button'] ?? 'Send enquiry');
    $requiredMarks = (bool) ($ui['required_marks'] ?? false);
    $showCity = (bool) ($ui['show_city'] ?? false);
    $hideProperty = (bool) ($ui['hide_property'] ?? false);
    $lockedProperty = (bool) ($ui['locked_property'] ?? false) || $hideProperty;
    $cities = $ui['cities'] ?? ['Pune', 'Mumbai', 'Gurgaon'];
    $req = static function () use ($requiredMarks): string {
        return $requiredMarks ? ' <span class="req">*</span>' : '';
    };
    $formAttrs = '';
    if ($lockedProperty && $propertyId !== '') {
        $formAttrs .= ' data-locked-property="' . h($propertyId) . '"';
    }
    ?>
    <form class="<?= h($formClass) ?>" method="post" action="<?= h(webPath('actions/visit_submit.php')) ?>" novalidate<?= $formAttrs ?>>
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="intent" value="<?= h($intent) ?>">
        <div class="hp-field" aria-hidden="true">
            <label for="<?= h($prefix) ?>-website">Website</label>
            <input id="<?= h($prefix) ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>
        <div class="field name-field">
            <label for="<?= h($prefix) ?>-name"><?= h($labels['name']) ?><?= $req() ?></label>
            <input id="<?= h($prefix) ?>-name" name="name" type="text" maxlength="100" autocomplete="name" placeholder="<?= h($placeholders['name']) ?>" required>
        </div>
        <div class="field phone-field">
            <label for="<?= h($prefix) ?>-phone"><?= h($labels['phone']) ?><?= $req() ?></label>
            <input type="hidden" name="country_code" value="+91">
            <input id="<?= h($prefix) ?>-phone" name="phone" type="tel" inputmode="numeric" maxlength="10" pattern="\d{10}" placeholder="<?= h($placeholders['phone']) ?>" required>
        </div>
        <div class="field email-field">
            <label for="<?= h($prefix) ?>-email"><?= h($labels['email']) ?><?= $req() ?></label>
            <input id="<?= h($prefix) ?>-email" name="email" type="email" maxlength="254" autocomplete="email" placeholder="<?= h($placeholders['email']) ?>" required>
        </div>
        <?php if ($showCity): ?>
            <div class="field city-field">
                <label for="<?= h($prefix) ?>-city"><?= h($labels['city']) ?><?= $req() ?></label>
                <select id="<?= h($prefix) ?>-city" name="preferred_city" required>
                    <option value="">Select city</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= h((string) $city) ?>"><?= h((string) $city) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
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
        <?php if ($hideProperty): ?>
            <input type="hidden" name="property_id" value="<?= h($propertyId) ?>" class="property-id">
        <?php else: ?>
            <div class="field property-field">
                <label for="<?= h($prefix) ?>-property-search"><?= h($labels['property']) ?></label>
                <div class="property-pick"<?= $lockedProperty ? ' data-locked="true"' : '' ?>>
                    <input
                        id="<?= h($prefix) ?>-property-search"
                        class="property-search"
                        type="text"
                        placeholder="<?= h($showCity ? 'Select a city first' : $placeholders['property']) ?>"
                        data-open-placeholder="<?= h($placeholders['property']) ?>"
                        autocomplete="off"
                        <?= $showCity ? 'disabled' : '' ?>
                        <?= $lockedProperty ? 'readonly' : '' ?>
                    >
                    <input type="hidden" name="property_id" value="<?= h($propertyId) ?>" class="property-id">
                    <ul class="property-results" hidden></ul>
                </div>
            </div>
        <?php endif; ?>
        <div class="field ai-message-field">
            <label for="<?= h($prefix) ?>-message"><?= h($labels['message']) ?><?= $req() ?></label>
            <div class="ai-message-control">
                <textarea id="<?= h($prefix) ?>-message" name="message" maxlength="5000" required placeholder="<?= h($placeholders['message']) ?>"></textarea>
                <span class="ai-message-icon" aria-hidden="true">↗</span>
            </div>
            <p class="ai-message-help">Press Shift + Enter for a new line.</p>
        </div>
        <p class="form-status" role="status"></p>
        <button class="btn send-btn" type="submit"><?= $buttonHtml ?></button>
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
