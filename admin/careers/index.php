<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/careers.php';

requireAdmin();
ensureCareerSchema();

$pdo = getDatabaseConnection();
$q = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$selectedId = (int) ($_GET['id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('careers/index.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? 'status');

    if ($action === 'delete' && $id > 0) {
        $app = careerApplicationById($id);
        if ($app !== null) {
            $absolute = careerResumeAbsolutePath((string) ($app['resume_path'] ?? ''));
            if ($absolute !== null) {
                @unlink($absolute);
            }
            $pdo->prepare('DELETE FROM career_applications WHERE id = ?')->execute([$id]);
            logAdminActivity('career_delete', 'career_application', $id, (string) $app['full_name']);
            adminFlash('success', 'Application deleted.');
        }
        adminRedirect('careers/index.php');
    }

    $newStatus = (string) ($_POST['status'] ?? 'New');
    $notes = trim((string) ($_POST['admin_notes'] ?? ''));
    if (!in_array($newStatus, careerStatusOptions(), true)) {
        $newStatus = 'New';
    }
    if ($id > 0) {
        $pdo->prepare(
            'UPDATE career_applications SET status = ?, admin_notes = ? WHERE id = ?'
        )->execute([$newStatus, $notes !== '' ? $notes : null, $id]);
        logAdminActivity('career_update', 'career_application', $id, $newStatus);
        adminFlash('success', 'Application updated.');
        adminRedirect('careers/index.php?id=' . $id);
    }
    adminRedirect('careers/index.php');
}

$sql = 'SELECT * FROM career_applications WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR role LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like];
}
if (in_array($statusFilter, careerStatusOptions(), true)) {
    $sql .= ' AND status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY created_at DESC, id DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$selected = null;
if ($selectedId > 0) {
    $selected = careerApplicationById($selectedId);
}

$stats = [
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM career_applications')->fetchColumn(),
    'new' => (int) $pdo->query("SELECT COUNT(*) FROM career_applications WHERE status = 'New'")->fetchColumn(),
    'reviewing' => (int) $pdo->query("SELECT COUNT(*) FROM career_applications WHERE status = 'Reviewing'")->fetchColumn(),
    'shortlisted' => (int) $pdo->query("SELECT COUNT(*) FROM career_applications WHERE status = 'Shortlisted'")->fetchColumn(),
];

adminLayoutStart('Careers', 'careers');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:12px;">
            <div>
                <h2 style="margin:0 0 6px;font-size:22px;">Career applications</h2>
                <p style="margin:0;color:#6d6458;">Review applications, download resumes and update status.</p>
            </div>
        </div>
        <div class="actions-row" style="gap:10px;flex-wrap:wrap;">
            <span class="help"><strong><?= (int) $stats['total'] ?></strong> total</span>
            <span class="help"><strong><?= (int) $stats['new'] ?></strong> new</span>
            <span class="help"><strong><?= (int) $stats['reviewing'] ?></strong> reviewing</span>
            <span class="help"><strong><?= (int) $stats['shortlisted'] ?></strong> shortlisted</span>
        </div>
    </div>

    <div class="panel">
        <form method="get" class="toolbar" style="margin:0;gap:10px;flex-wrap:wrap;">
            <input type="search" name="q" value="<?= h($q) ?>" placeholder="Search name, email, role…" style="min-width:220px;">
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach (careerStatusOptions() as $option): ?>
                    <option value="<?= h($option) ?>"<?= $statusFilter === $option ? ' selected' : '' ?>><?= h($option) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn small" type="submit">Filter</button>
        </form>
    </div>

    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Role</th>
                    <th>Experience</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6">No applications yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <strong><?= h((string) $row['full_name']) ?></strong>
                        <div class="muted"><?= h((string) $row['email']) ?></div>
                    </td>
                    <td><?= h((string) $row['role']) ?></td>
                    <td><?= h((string) ($row['experience'] ?? '—')) ?></td>
                    <td><?= h((string) $row['status']) ?></td>
                    <td><?= h((string) $row['created_at']) ?></td>
                    <td><a class="btn small secondary" href="<?= h(adminUrl('careers/index.php?id=' . (int) $row['id'])) ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($selected !== null): ?>
        <div class="panel stack">
            <div class="toolbar" style="margin:0;">
                <h3 style="margin:0;">Application #<?= (int) $selected['id'] ?></h3>
                <a class="btn small secondary" href="<?= h(adminUrl('careers/index.php')) ?>">Close</a>
            </div>
            <p style="margin:0;">
                <strong><?= h((string) $selected['full_name']) ?></strong><br>
                <?= h((string) $selected['email']) ?> ·
                <?= h((string) $selected['country_code']) ?> <?= h((string) $selected['phone']) ?>
            </p>
            <p style="margin:0;" class="help">
                Role: <?= h((string) $selected['role']) ?><br>
                Experience: <?= h((string) ($selected['experience'] ?? '—')) ?><br>
                Notice period: <?= h((string) ($selected['notice_period'] ?? '—')) ?><br>
                Submitted: <?= h((string) $selected['created_at']) ?>
            </p>
            <?php if (trim((string) ($selected['message'] ?? '')) !== ''): ?>
                <div>
                    <strong>About</strong>
                    <p style="white-space:pre-wrap;margin:8px 0 0;"><?= h((string) $selected['message']) ?></p>
                </div>
            <?php endif; ?>
            <div class="actions-row">
                <a class="btn small" href="<?= h(adminUrl('careers/download.php?id=' . (int) $selected['id'])) ?>">
                    Download resume<?= trim((string) ($selected['resume_original_name'] ?? '')) !== '' ? ' (' . h((string) $selected['resume_original_name']) . ')' : '' ?>
                </a>
            </div>
            <form method="post" class="stack">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $selected['id'] ?>">
                <input type="hidden" name="action" value="status">
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach (careerStatusOptions() as $option): ?>
                            <option value="<?= h($option) ?>"<?= (string) $selected['status'] === $option ? ' selected' : '' ?>><?= h($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="admin_notes">Admin notes</label>
                    <textarea id="admin_notes" name="admin_notes" rows="4"><?= h((string) ($selected['admin_notes'] ?? '')) ?></textarea>
                </div>
                <div class="actions-row">
                    <button class="btn" type="submit">Save</button>
                </div>
            </form>
            <form method="post" onsubmit="return confirm('Delete this application permanently?');">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $selected['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn small secondary" type="submit">Delete application</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php adminLayoutEnd(); ?>
