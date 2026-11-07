<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/settings.php';

$user = requireAdmin();
$pdo = getDatabaseConnection();

$tab = (string) ($_GET['tab'] ?? 'terms');
if (!in_array($tab, ['terms', 'privacy'], true)) {
    $tab = 'terms';
}

$mode = (string) ($_GET['mode'] ?? 'view');
if (!in_array($mode, ['view', 'edit'], true)) {
    $mode = 'view';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('legal.php', ['tab' => $tab, 'mode' => 'edit']);
    }

    $saveTab = (string) ($_POST['tab'] ?? 'terms');
    if (!in_array($saveTab, ['terms', 'privacy'], true)) {
        $saveTab = 'terms';
    }

    $prefix = $saveTab === 'privacy' ? 'legal_privacy' : 'legal_terms';
    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));

    if ($title === '' || $body === '') {
        adminFlash('error', 'Title and content are required.');
        adminRedirect('legal.php', ['tab' => $saveTab, 'mode' => 'edit']);
    }

    if (mb_strlen($title) > 180) {
        adminFlash('error', 'Title is too long.');
        adminRedirect('legal.php', ['tab' => $saveTab, 'mode' => 'edit']);
    }

    try {
        upsertSiteSetting($prefix . '_title', $title);
        upsertSiteSetting($prefix . '_body', $body);
        upsertSiteSetting($prefix . '_updated_at', date('c'));
        logAdminActivity('legal_update', 'legal', null, $saveTab);
        adminFlash('success', ($saveTab === 'privacy' ? 'Privacy Policy' : 'Terms & Conditions') . ' saved.');
        adminRedirect('legal.php', ['tab' => $saveTab, 'mode' => 'view']);
    } catch (Throwable $e) {
        error_log('Legal save failed: ' . $e->getMessage());
        adminFlash('error', 'Could not save legal page.');
        adminRedirect('legal.php', ['tab' => $saveTab, 'mode' => 'edit']);
    }
}

$content = legalPageContent($tab);
$pageLabel = $tab === 'privacy' ? 'Privacy Policy' : 'Terms & Conditions';
$publicPath = $tab === 'privacy' ? 'pages/privacy-policy' : 'pages/terms-conditions';

adminLayoutStart('Legal pages', 'legal');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:0;">
            <div class="legal-tabs">
                <a class="btn small <?= $tab === 'terms' ? '' : 'secondary' ?>" href="<?= h(adminUrl('legal?tab=terms&mode=' . $mode)) ?>">Terms &amp; Conditions</a>
                <a class="btn small <?= $tab === 'privacy' ? '' : 'secondary' ?>" href="<?= h(adminUrl('legal?tab=privacy&mode=' . $mode)) ?>">Privacy Policy</a>
            </div>
            <div class="actions-row">
                <?php if ($mode === 'view'): ?>
                    <a class="btn small" href="<?= h(adminUrl('legal?tab=' . $tab . '&mode=edit')) ?>">Edit</a>
                <?php else: ?>
                    <a class="btn small secondary" href="<?= h(adminUrl('legal?tab=' . $tab . '&mode=view')) ?>">Cancel</a>
                <?php endif; ?>
                <a class="btn small secondary" href="<?= h(webPath($publicPath)) ?>" target="_blank" rel="noopener">View on site</a>
            </div>
        </div>
        <p class="help" style="margin-top:12px;">
            Editing <strong><?= h($pageLabel) ?></strong> only. The other legal page is untouched, so Terms and Privacy never overwrite each other.
        </p>
    </div>

    <?php if ($mode === 'edit'): ?>
        <form class="panel" method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="tab" value="<?= h($tab) ?>">
            <h3>Edit <?= h($pageLabel) ?></h3>
            <div class="form-grid">
                <div class="field full">
                    <label for="legal_title">Page title</label>
                    <input id="legal_title" name="title" maxlength="180" required value="<?= h($content['title']) ?>">
                </div>
                <div class="field full">
                    <label for="legal_body">Content</label>
                    <textarea id="legal_body" name="body" required class="legal-editor"><?= h($content['body']) ?></textarea>
                    <p class="help">
                        Plain text. Use blank lines between sections. Start section headings like <code>1. Section title</code> so the public page formats them as headings.
                    </p>
                </div>
            </div>
            <div class="actions-row" style="margin-top:14px;">
                <button class="btn" type="submit">Save <?= h($pageLabel) ?></button>
                <a class="btn secondary" href="<?= h(adminUrl('legal?tab=' . $tab . '&mode=view')) ?>">Back to preview</a>
            </div>
        </form>
    <?php else: ?>
        <div class="panel legal-preview">
            <div class="toolbar" style="margin-bottom:8px;">
                <h3 style="margin:0;"><?= h($content['title']) ?></h3>
                <?php if ($content['updated_at'] !== ''): ?>
                    <span class="muted">Saved <?= h(date('d M Y, H:i', strtotime($content['updated_at']) ?: time())) ?></span>
                <?php endif; ?>
            </div>
            <div class="legal-preview-body">
                <?= renderLegalBody($content['body']) ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php adminLayoutEnd(); ?>
