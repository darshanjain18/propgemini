<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/notifications.php';

$user = requireAdmin();
$pdo = getDatabaseConnection();

$keys = [
    'whatsapp_number',
    'contact_phone',
    'contact_email',
    'contact_address',
    'about_blurb',
    'emi_default_loan',
    'emi_default_rate',
    'emi_default_years',
    'social_instagram',
    'social_facebook',
    'social_linkedin',
    'social_x',
    'careers_roles',
];

$settings = [];
$stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
foreach ($stmt->fetchAll() as $row) {
    $settings[$row['setting_key']] = (string) ($row['setting_value'] ?? '');
}

function cleanOptionalUrl(string $raw, string $label): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $raw)) {
        $raw = 'https://' . $raw;
    }
    if (filter_var($raw, FILTER_VALIDATE_URL) === false) {
        throw new RuntimeException($label . ' must be a valid URL, or left blank.');
    }
    return $raw;
}

function normalizeIndianMobile(string $raw): string
{
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if (str_starts_with($digits, '91') && strlen($digits) === 12) {
        $digits = substr($digits, 2);
    }
    if (strlen($digits) !== 10) {
        throw new RuntimeException('Enter a valid 10-digit Indian mobile number.');
    }
    return $digits;
}

$whatsappDigits = preg_replace('/\D+/', '', $settings['whatsapp_number'] ?? '') ?? '';
if (str_starts_with($whatsappDigits, '91') && strlen($whatsappDigits) === 12) {
    $whatsappDigits = substr($whatsappDigits, 2);
}
$whatsappDigits = substr($whatsappDigits, -10);

$contactDigits = preg_replace('/\D+/', '', $settings['contact_phone'] ?? '') ?? '';
if (str_starts_with($contactDigits, '91') && strlen($contactDigits) === 12) {
    $contactDigits = substr($contactDigits, 2);
}
$contactDigits = substr($contactDigits, -10);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('settings.php');
    }

    $action = (string) ($_POST['action'] ?? 'settings');

    try {
        if ($action === 'settings') {
            $whatsapp = normalizeIndianMobile((string) ($_POST['whatsapp_digits'] ?? ''));
            $contactPhone = normalizeIndianMobile((string) ($_POST['contact_phone_digits'] ?? ''));

            $values = [
                'whatsapp_number' => '91' . $whatsapp,
                'contact_phone' => '+91 ' . $contactPhone,
                'contact_email' => trim((string) ($_POST['contact_email'] ?? '')),
                'contact_address' => trim((string) ($_POST['contact_address'] ?? '')),
                'about_blurb' => trim((string) ($_POST['about_blurb'] ?? '')),
                'emi_default_loan' => trim((string) ($_POST['emi_default_loan'] ?? '')),
                'emi_default_rate' => trim((string) ($_POST['emi_default_rate'] ?? '')),
                'emi_default_years' => trim((string) ($_POST['emi_default_years'] ?? '')),
                'social_instagram' => cleanOptionalUrl((string) ($_POST['social_instagram'] ?? ''), 'Instagram'),
                'social_facebook' => cleanOptionalUrl((string) ($_POST['social_facebook'] ?? ''), 'Facebook'),
                'social_linkedin' => cleanOptionalUrl((string) ($_POST['social_linkedin'] ?? ''), 'LinkedIn'),
                'social_x' => cleanOptionalUrl((string) ($_POST['social_x'] ?? ''), 'X'),
                'careers_roles' => trim((string) ($_POST['careers_roles'] ?? '')),
            ];

            $upsert = $pdo->prepare(
                'INSERT INTO site_settings (setting_key, setting_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach ($keys as $key) {
                $upsert->execute([$key, $values[$key] ?? '']);
            }
            logAdminActivity('settings_update');
            adminFlash('success', 'Settings saved.');
        }

        if ($action === 'password') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            if (strlen($new) < 8) {
                throw new RuntimeException('New password must be at least 8 characters.');
            }
            if ($new !== $confirm) {
                throw new RuntimeException('Password confirmation does not match.');
            }

            $rowStmt = $pdo->prepare('SELECT password FROM admins WHERE id = ?');
            $rowStmt->execute([(int) $user['id']]);
            $hash = (string) $rowStmt->fetchColumn();
            if (!password_verify($current, $hash)) {
                throw new RuntimeException('Current password is incorrect.');
            }

            $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?')->execute([
                password_hash($new, PASSWORD_DEFAULT),
                (int) $user['id'],
            ]);
            logAdminActivity('password_change');
            adminFlash('success', 'Password updated.');
        }
    } catch (Throwable $e) {
        adminFlash('error', $e->getMessage());
    }

    adminRedirect('settings.php');
}

$setupGaps = [];
if (isPlaceholderContactValue('whatsapp_number', (string) ($settings['whatsapp_number'] ?? ''))) {
    $setupGaps[] = 'WhatsApp number';
}
if (isPlaceholderContactValue('contact_phone', (string) ($settings['contact_phone'] ?? ''))) {
    $setupGaps[] = 'Contact phone';
}
if (isPlaceholderContactValue('contact_email', (string) ($settings['contact_email'] ?? ''))) {
    $setupGaps[] = 'Contact email';
}
if (trim((string) ($settings['contact_address'] ?? '')) === '') {
    $setupGaps[] = 'Contact address';
}

adminLayoutStart('Settings', 'settings');
?>
<div class="stack">
    <?php if ($setupGaps !== []): ?>
        <div class="flash flash-error" style="margin:0;">
            Public contact still needs: <?= h(implode(', ', $setupGaps)) ?>.
            These values power the footer, Contact page and lead alerts.
            <a class="text-link" href="<?= h(adminUrl('guide.php')) ?>">Open getting started</a>
        </div>
    <?php endif; ?>
    <form class="panel" method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="settings">
        <h3>Site settings</h3>
        <div class="form-grid">
            <div class="field">
                <label for="whatsapp_digits">WhatsApp number</label>
                <div class="phone-row">
                    <div class="prefix">+91</div>
                    <input id="whatsapp_digits" name="whatsapp_digits" inputmode="numeric" maxlength="10" pattern="\d{10}" required placeholder="9876543210" value="<?= h($whatsappDigits) ?>">
                </div>
                <p class="help">Exactly 10 digits. Updates the site WhatsApp button and footer chat link.</p>
            </div>
            <div class="field">
                <label for="contact_phone_digits">Contact phone</label>
                <div class="phone-row">
                    <div class="prefix">+91</div>
                    <input id="contact_phone_digits" name="contact_phone_digits" inputmode="numeric" maxlength="10" pattern="\d{10}" required placeholder="9876543210" value="<?= h($contactDigits) ?>">
                </div>
                <p class="help">Exactly 10 digits. Shown in the public footer as +91 XXXXX XXXXX.</p>
            </div>
            <div class="field">
                <label for="contact_email">Contact email</label>
                <input id="contact_email" name="contact_email" type="email" value="<?= h($settings['contact_email'] ?? '') ?>">
                <p class="help">Shown in the public footer.</p>
            </div>
            <div class="field">
                <label for="contact_address">Contact address</label>
                <input id="contact_address" name="contact_address" value="<?= h($settings['contact_address'] ?? '') ?>">
            </div>
            <div class="field full">
                <label for="about_blurb">About blurb</label>
                <textarea id="about_blurb" name="about_blurb"><?= h($settings['about_blurb'] ?? '') ?></textarea>
            </div>
            <div class="field">
                <label for="emi_default_loan">EMI default loan amount</label>
                <input id="emi_default_loan" name="emi_default_loan" value="<?= h($settings['emi_default_loan'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="emi_default_rate">EMI default rate (%)</label>
                <input id="emi_default_rate" name="emi_default_rate" value="<?= h($settings['emi_default_rate'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="emi_default_years">EMI default tenure (years)</label>
                <input id="emi_default_years" name="emi_default_years" value="<?= h($settings['emi_default_years'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="social_instagram">Instagram URL</label>
                <input id="social_instagram" name="social_instagram" value="<?= h($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
            </div>
            <div class="field">
                <label for="social_facebook">Facebook URL</label>
                <input id="social_facebook" name="social_facebook" value="<?= h($settings['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
            </div>
            <div class="field">
                <label for="social_linkedin">LinkedIn URL</label>
                <input id="social_linkedin" name="social_linkedin" value="<?= h($settings['social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/...">
            </div>
            <div class="field">
                <label for="social_x">X (Twitter) URL</label>
                <input id="social_x" name="social_x" value="<?= h($settings['social_x'] ?? ($settings['social_youtube'] ?? '')) ?>" placeholder="https://x.com/...">
            </div>
            <div class="field full">
                <label for="careers_roles">Open roles</label>
                <textarea id="careers_roles" name="careers_roles" placeholder="Property Advisor | Pune | Full-time"><?= h($settings['careers_roles'] ?? '') ?></textarea>
                <p class="help">One role per line: Title | Location | Type. Leave blank to use the default three roles. Icons appear in the footer only when a URL is saved.</p>
            </div>
        </div>
        <div class="actions-row" style="margin-top:14px;">
            <button class="btn" type="submit">Save settings</button>
        </div>
    </form>

    <form class="panel" method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="password">
        <h3>Change password</h3>
        <div class="form-grid">
            <div class="field">
                <label for="current_password">Current password</label>
                <div class="password-wrap">
                    <input id="current_password" name="current_password" type="password" required>
                    <button type="button" class="password-toggle" data-toggle-password="current_password">Show</button>
                </div>
            </div>
            <div class="field">
                <label for="new_password">New password</label>
                <div class="password-wrap">
                    <input id="new_password" name="new_password" type="password" required minlength="8">
                    <button type="button" class="password-toggle" data-toggle-password="new_password">Show</button>
                </div>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm new password</label>
                <div class="password-wrap">
                    <input id="confirm_password" name="confirm_password" type="password" required minlength="8">
                    <button type="button" class="password-toggle" data-toggle-password="confirm_password">Show</button>
                </div>
            </div>
        </div>
        <div class="actions-row" style="margin-top:14px;">
            <button class="btn" type="submit">Update password</button>
        </div>
        <p class="help">Signed in as <?= h((string) $user['email']) ?>.</p>
    </form>
</div>
<?php adminLayoutEnd(); ?>
