<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';

$pdo = getDatabaseConnection();
$status = trim((string) ($_GET['status'] ?? ''));
$selectedId = (int) ($_GET['id'] ?? 0);
$view = (string) ($_GET['view'] ?? 'list');
if (!in_array($view, ['list', 'calendar'], true)) {
    $view = 'list';
}
$monthParam = trim((string) ($_GET['month'] ?? date('Y-m')));
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
    $monthParam = date('Y-m');
}
$monthStart = DateTimeImmutable::createFromFormat('!Y-m-d', $monthParam . '-01') ?: new DateTimeImmutable('first day of this month');
$monthEnd = $monthStart->modify('last day of this month');
$prevMonth = $monthStart->modify('-1 month')->format('Y-m');
$nextMonth = $monthStart->modify('+1 month')->format('Y-m');

$allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rescheduled'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    requireAdmin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('visits/index.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $newStatus = (string) ($_POST['status'] ?? 'Pending');
    if (!in_array($newStatus, $allowedStatuses, true)) {
        $newStatus = 'Pending';
    }
    $notes = trim((string) ($_POST['admin_notes'] ?? ''));
    $visitDate = trim((string) ($_POST['visit_date'] ?? ''));
    $visitTime = trim((string) ($_POST['visit_time'] ?? ''));
    $rescheduleDate = trim((string) ($_POST['reschedule_date'] ?? ''));
    $rescheduleTime = trim((string) ($_POST['reschedule_time'] ?? ''));

    if ($newStatus === 'Rescheduled') {
        if ($rescheduleDate !== '') {
            $visitDate = $rescheduleDate;
        }
        if ($rescheduleTime !== '') {
            $visitTime = $rescheduleTime;
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE site_visit_requests
         SET status = ?,
             admin_notes = ?,
             visit_date = ?,
             visit_time = ?,
             reschedule_date = ?,
             reschedule_time = ?
         WHERE id = ?'
    );
    $stmt->execute([
        $newStatus,
        $notes !== '' ? $notes : null,
        $visitDate !== '' ? $visitDate : null,
        $visitTime !== '' ? $visitTime : null,
        $rescheduleDate !== '' ? $rescheduleDate : null,
        $rescheduleTime !== '' ? $rescheduleTime : null,
        $id,
    ]);
    logAdminActivity('visit_update', 'site_visit', $id, $newStatus);
    adminFlash('success', 'Site visit updated.');
    adminRedirect('visits/index.php?id=' . $id . '&view=' . rawurlencode($view) . '&month=' . rawurlencode($monthParam));
}

$pipeline = [
    'today' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE visit_date = CURDATE() AND status IN ('Pending','Confirmed','Rescheduled')")->fetchColumn(),
    'upcoming' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE visit_date > CURDATE() AND status IN ('Pending','Confirmed','Rescheduled')")->fetchColumn(),
    'Pending' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE status = 'Pending'")->fetchColumn(),
    'Confirmed' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE status = 'Confirmed'")->fetchColumn(),
    'Completed' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE status = 'Completed'")->fetchColumn(),
    'Cancelled' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE status = 'Cancelled'")->fetchColumn(),
    'Rescheduled' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE status = 'Rescheduled'")->fetchColumn(),
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM site_visit_requests')->fetchColumn(),
];
$conversion = $pipeline['total'] > 0
    ? (int) round(($pipeline['Completed'] / $pipeline['total']) * 100)
    : 0;

$sql = "SELECT v.*, p.name AS property_name
        FROM site_visit_requests v
        LEFT JOIN properties p ON p.id = v.property_id
        WHERE 1=1";
$params = [];
if ($status !== '' && in_array($status, $allowedStatuses, true)) {
    $sql .= ' AND v.status = :status';
    $params['status'] = $status;
} elseif ($status === 'today') {
    $sql .= " AND v.visit_date = CURDATE() AND v.status IN ('Pending','Confirmed','Rescheduled')";
} elseif ($status === 'upcoming') {
    $sql .= " AND v.visit_date > CURDATE() AND v.status IN ('Pending','Confirmed','Rescheduled')";
}
$sql .= ' ORDER BY COALESCE(v.visit_date, DATE(v.created_at)) DESC, v.visit_time DESC, v.id DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$calendarRows = $pdo->prepare(
    "SELECT v.*, p.name AS property_name
     FROM site_visit_requests v
     LEFT JOIN properties p ON p.id = v.property_id
     WHERE v.visit_date BETWEEN ? AND ?
     ORDER BY v.visit_date ASC, v.visit_time ASC"
);
$calendarRows->execute([$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
$calendarVisits = $calendarRows->fetchAll();
$byDate = [];
foreach ($calendarVisits as $visit) {
    $dateKey = (string) ($visit['visit_date'] ?? '');
    if ($dateKey === '') {
        continue;
    }
    $byDate[$dateKey][] = $visit;
}

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
            'SELECT v.*, p.name AS property_name
             FROM site_visit_requests v
             LEFT JOIN properties p ON p.id = v.property_id
             WHERE v.id = ?'
        );
        $one->execute([$selectedId]);
        $selected = $one->fetch() ?: null;
    }
}

adminLayoutStart('Site visits', 'visits');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Visit pipeline</p>
        <h2>Site visit management</h2>
        <p>Track today’s schedule, upcoming confirmations, reschedules and conversion across the portfolio.</p>
    </div>
    <div class="dash-actions">
        <a class="btn <?= $view === 'list' ? '' : 'secondary' ?>" href="<?= h(adminUrl('visits/index?view=list' . ($status !== '' ? '&status=' . rawurlencode($status) : ''))) ?>">List view</a>
        <a class="btn <?= $view === 'calendar' ? '' : 'secondary' ?>" href="<?= h(adminUrl('visits/index?view=calendar&month=' . rawurlencode($monthParam))) ?>">Calendar</a>
    </div>
</section>

<div class="visit-pipeline-grid">
    <a class="visit-pipe-card<?= $status === 'today' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=today')) ?>"><span>Today</span><strong><?= $pipeline['today'] ?></strong></a>
    <a class="visit-pipe-card<?= $status === 'upcoming' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=upcoming')) ?>"><span>Upcoming</span><strong><?= $pipeline['upcoming'] ?></strong></a>
    <a class="visit-pipe-card<?= $status === 'Pending' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=Pending')) ?>"><span>Pending confirmation</span><strong><?= $pipeline['Pending'] ?></strong></a>
    <a class="visit-pipe-card<?= $status === 'Confirmed' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=Confirmed')) ?>"><span>Confirmed</span><strong><?= $pipeline['Confirmed'] ?></strong></a>
    <a class="visit-pipe-card<?= $status === 'Completed' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=Completed')) ?>"><span>Completed</span><strong><?= $pipeline['Completed'] ?></strong></a>
    <a class="visit-pipe-card<?= $status === 'Cancelled' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=Cancelled')) ?>"><span>Cancelled</span><strong><?= $pipeline['Cancelled'] ?></strong></a>
    <a class="visit-pipe-card<?= $status === 'Rescheduled' ? ' is-on' : '' ?>" href="<?= h(adminUrl('visits/index?view=list&status=Rescheduled')) ?>"><span>Rescheduled</span><strong><?= $pipeline['Rescheduled'] ?></strong></a>
    <div class="visit-pipe-card is-static"><span>Conversion rate</span><strong><?= $conversion ?>%</strong></div>
</div>

<?php if ($view === 'calendar'): ?>
    <div class="panel visit-calendar-panel">
        <div class="toolbar">
            <div class="actions-row">
                <a class="btn small secondary" href="<?= h(adminUrl('visits/index?view=calendar&month=' . rawurlencode($prevMonth))) ?>">← Prev</a>
                <strong><?= h($monthStart->format('F Y')) ?></strong>
                <a class="btn small secondary" href="<?= h(adminUrl('visits/index?view=calendar&month=' . rawurlencode($nextMonth))) ?>">Next →</a>
            </div>
            <a class="btn small secondary" href="<?= h(adminUrl('visits/index?view=calendar&month=' . rawurlencode(date('Y-m')))) ?>">This month</a>
        </div>
        <div class="visit-calendar">
            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow): ?>
                <div class="visit-cal-dow"><?= $dow ?></div>
            <?php endforeach; ?>
            <?php
            $startDow = (int) $monthStart->format('N'); // 1=Mon
            for ($i = 1; $i < $startDow; $i++): ?>
                <div class="visit-cal-day is-empty"></div>
            <?php endfor;
            for ($day = 1; $day <= (int) $monthEnd->format('j'); $day++):
                $dateKey = $monthStart->format('Y-m') . '-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                $items = $byDate[$dateKey] ?? [];
                $isToday = $dateKey === date('Y-m-d');
                ?>
                <div class="visit-cal-day<?= $isToday ? ' is-today' : '' ?>">
                    <div class="visit-cal-date"><?= $day ?></div>
                    <?php foreach (array_slice($items, 0, 3) as $item): ?>
                        <a class="visit-cal-chip badge <?= h(strtolower((string) $item['status'])) ?>" href="<?= h(adminUrl('visits/index?view=list&id=' . (int) $item['id'])) ?>">
                            <?= h(substr((string) ($item['visit_time'] ?? ''), 0, 5)) ?> <?= h((string) $item['name']) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (count($items) > 3): ?>
                        <div class="muted">+<?= count($items) - 3 ?> more</div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
<?php else: ?>
    <div class="toolbar">
        <form class="filters filters-row" method="get">
            <input type="hidden" name="view" value="list">
            <select name="status">
                <option value="">All visits</option>
                <option value="today"<?= $status === 'today' ? ' selected' : '' ?>>Today</option>
                <option value="upcoming"<?= $status === 'upcoming' ? ' selected' : '' ?>>Upcoming</option>
                <?php foreach ($allowedStatuses as $option): ?>
                    <option value="<?= h($option) ?>"<?= $status === $option ? ' selected' : '' ?>><?= h($option) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn secondary" type="submit">Filter</button>
            <?php if ($status !== ''): ?>
                <a class="btn secondary" href="<?= h(adminUrl('visits/index?view=list')) ?>">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="form-grid" style="align-items:start;">
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Visitor</th><th>Property</th><th>Preferred</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php if ($rows === []): ?>
                    <tr><td colspan="4" class="muted">No site visit requests in this filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td>
                                <a href="<?= h(adminUrl('visits/index?view=list&id=' . (int) $row['id'] . ($status !== '' ? '&status=' . rawurlencode($status) : ''))) ?>">
                                    <strong><?= h((string) $row['name']) ?></strong>
                                </a>
                                <div class="muted"><?= h((string) ($row['country_code'] ?? '')) ?> <?= h((string) $row['contact_number']) ?></div>
                            </td>
                            <td><?= h((string) ($row['property_name'] ?? '—')) ?></td>
                            <td class="muted"><?= h(trim(($row['visit_date'] ?? '') . ' ' . substr((string) ($row['visit_time'] ?? ''), 0, 5))) ?></td>
                            <td><span class="badge <?= h(strtolower((string) $row['status'])) ?>"><?= h((string) $row['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <?php if ($selected === null): ?>
                <p class="muted" style="margin:0;">Select a visit request to update status, notes or reschedule.</p>
            <?php else: ?>
                <h3 style="margin-top:0;"><?= h((string) $selected['name']) ?></h3>
                <p class="muted"><?= h((string) $selected['email']) ?> · <?= h((string) ($selected['country_code'] ?? '')) ?> <?= h((string) $selected['contact_number']) ?></p>
                <p><strong>Property:</strong> <?= h((string) ($selected['property_name'] ?? '—')) ?></p>
                <p><?= nl2br(h((string) ($selected['message'] ?? ''))) ?></p>
                <form method="post" class="stack">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $selected['id'] ?>">
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach ($allowedStatuses as $option): ?>
                                <option value="<?= h($option) ?>"<?= ((string) $selected['status'] === $option) ? ' selected' : '' ?>><?= h($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grid" style="margin:0;">
                        <div class="field">
                            <label for="visit_date">Visit date</label>
                            <input id="visit_date" name="visit_date" type="date" value="<?= h((string) ($selected['visit_date'] ?? '')) ?>">
                        </div>
                        <div class="field">
                            <label for="visit_time">Visit time</label>
                            <input id="visit_time" name="visit_time" type="time" value="<?= h(substr((string) ($selected['visit_time'] ?? ''), 0, 5)) ?>">
                        </div>
                        <div class="field">
                            <label for="reschedule_date">Reschedule date</label>
                            <input id="reschedule_date" name="reschedule_date" type="date" value="<?= h((string) ($selected['reschedule_date'] ?? '')) ?>">
                        </div>
                        <div class="field">
                            <label for="reschedule_time">Reschedule time</label>
                            <input id="reschedule_time" name="reschedule_time" type="time" value="<?= h(substr((string) ($selected['reschedule_time'] ?? ''), 0, 5)) ?>">
                        </div>
                    </div>
                    <p class="help">When status is Rescheduled, the reschedule date/time also becomes the active visit slot.</p>
                    <div class="field">
                        <label for="admin_notes">Admin notes</label>
                        <textarea id="admin_notes" name="admin_notes"><?= h((string) ($selected['admin_notes'] ?? '')) ?></textarea>
                    </div>
                    <button class="btn" type="submit">Save visit</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php adminLayoutEnd(); ?>
