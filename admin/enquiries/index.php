<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';

$pdo = getDatabaseConnection();
$status = trim((string) ($_GET['status'] ?? ''));
$source = trim((string) ($_GET['source'] ?? ''));
$selectedId = (int) ($_GET['id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    requireAdmin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('enquiries/index.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $newStatus = (string) ($_POST['status'] ?? 'new');
    $allowed = ['new', 'contacted', 'qualified', 'closed', 'spam'];
    if (!in_array($newStatus, $allowed, true)) {
        $newStatus = 'new';
    }
    $notes = trim((string) ($_POST['admin_notes'] ?? ''));
    $followUp = trim((string) ($_POST['follow_up_date'] ?? ''));

    $stmt = $pdo->prepare(
        'UPDATE enquiries
         SET status = ?, admin_notes = ?, follow_up_date = ?, assigned_admin_id = ?
         WHERE id = ?'
    );
    $stmt->execute([
        $newStatus,
        $notes !== '' ? $notes : null,
        $followUp !== '' ? $followUp : null,
        (int) ($_SESSION['admin_id'] ?? 0) ?: null,
        $id,
    ]);
    logAdminActivity('enquiry_update', 'enquiry', $id, $newStatus);
    adminFlash('success', 'Enquiry updated.');
    adminRedirect('enquiries/index.php?id=' . $id);
}

$sql = "SELECT e.*, p.name AS property_name
        FROM enquiries e
        LEFT JOIN properties p ON p.id = e.property_id
        WHERE 1=1";
$params = [];
if ($status !== '') {
    $sql .= ' AND e.status = :status';
    $params['status'] = $status;
}
if ($source !== '') {
    $sql .= ' AND e.source = :source';
    $params['source'] = $source;
}
$sql .= ' ORDER BY e.created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$selected = null;
if ($selectedId > 0) {
    foreach ($rows as $row) {
        if ((int) $row['id'] === $selectedId) {
            $selected = $row;
            break;
        }
    }
    if ($selected === null) {
        $one = $pdo->prepare(
            'SELECT e.*, p.name AS property_name FROM enquiries e
             LEFT JOIN properties p ON p.id = e.property_id WHERE e.id = ?'
        );
        $one->execute([$selectedId]);
        $selected = $one->fetch() ?: null;
    }
}

adminLayoutStart('Enquiries', 'enquiries');
$exportQuery = http_build_query(array_filter([
    'status' => $status !== '' ? $status : null,
    'source' => $source !== '' ? $source : null,
]));
?>
<div class="toolbar">
    <form class="filters filters-row" method="get">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['new', 'contacted', 'qualified', 'closed', 'spam'] as $option): ?>
                <option value="<?= h($option) ?>"<?= $status === $option ? ' selected' : '' ?>><?= h($option) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="source">
            <option value="">All sources</option>
            <?php foreach (['contact', 'visit-form', 'website', 'whatsapp-consent'] as $option): ?>
                <option value="<?= h($option) ?>"<?= $source === $option ? ' selected' : '' ?>><?= h($option) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn secondary" type="submit">Filter</button>
    </form>
    <a class="btn" href="<?= h(adminUrl('enquiries/export' . ($exportQuery !== '' ? '?' . $exportQuery : ''))) ?>">Export CSV / Excel</a>
</div>

<div class="form-grid" style="align-items:start;">
    <div class="table-wrap">
        <table>
            <thead>
            <tr><th>Lead</th><th>Property</th><th>Status</th><th>When</th></tr>
            </thead>
            <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="4" class="muted">No enquiries found.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <a href="<?= h(adminUrl('enquiries/index.php?id=' . (int) $row['id'] . ($status !== '' ? '&status=' . rawurlencode($status) : '') . ($source !== '' ? '&source=' . rawurlencode($source) : ''))) ?>">
                                <strong><?= h((string) $row['name']) ?></strong>
                            </a>
                            <div class="muted"><?= h((string) $row['phone']) ?> · <?= h((string) ($row['subject'] ?? '')) ?></div>
                        </td>
                        <td><?= h((string) ($row['property_name'] ?? '—')) ?></td>
                        <td><span class="badge <?= h((string) $row['status']) ?>"><?= h((string) $row['status']) ?></span></td>
                        <td class="muted"><?= h((string) $row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <?php if ($selected === null): ?>
            <p class="muted" style="margin:0;">Select an enquiry to update status and notes.</p>
        <?php else: ?>
            <h3 style="margin-top:0;"><?= h((string) $selected['name']) ?></h3>
            <p class="muted"><?= h((string) $selected['email']) ?> · <?= h((string) $selected['phone']) ?></p>
            <p><strong>Source:</strong> <?= h((string) $selected['source']) ?><br>
               <strong>Property:</strong> <?= h((string) ($selected['property_name'] ?? '—')) ?></p>
            <p><?= nl2br(h((string) $selected['message'])) ?></p>
            <form method="post" class="stack">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $selected['id'] ?>">
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach (['new', 'contacted', 'qualified', 'closed', 'spam'] as $option): ?>
                            <option value="<?= h($option) ?>"<?= ((string) $selected['status'] === $option) ? ' selected' : '' ?>><?= h($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="follow_up_date">Follow-up date</label>
                    <input id="follow_up_date" name="follow_up_date" type="datetime-local"
                           value="<?= h($selected['follow_up_date'] ? date('Y-m-d\TH:i', strtotime((string) $selected['follow_up_date'])) : '') ?>">
                </div>
                <div class="field">
                    <label for="admin_notes">Admin notes</label>
                    <textarea id="admin_notes" name="admin_notes"><?= h((string) ($selected['admin_notes'] ?? '')) ?></textarea>
                </div>
                <button class="btn" type="submit">Save enquiry</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php adminLayoutEnd(); ?>
