<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

requireAdmin();

$pdo = getDatabaseConnection();
$status = trim((string) ($_GET['status'] ?? ''));
$source = trim((string) ($_GET['source'] ?? ''));

$sql = "SELECT e.id, e.name, e.email, e.phone, e.subject, e.message, e.status, e.source,
               e.whatsapp_consent, e.follow_up_date, e.created_at, e.updated_at,
               p.name AS property_name
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
$sql .= ' ORDER BY e.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

logAdminActivity('enquiry_export', 'enquiry', null, 'rows=' . count($rows));

$filename = 'propgemini-enquiries-' . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
if ($out === false) {
    http_response_code(500);
    exit('Unable to export.');
}

// Excel-friendly BOM
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, [
    'ID', 'Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'Source',
    'WhatsApp consent', 'Property', 'Follow-up', 'Created at', 'Updated at',
]);

foreach ($rows as $row) {
    fputcsv($out, [
        (int) $row['id'],
        (string) $row['name'],
        (string) $row['email'],
        (string) $row['phone'],
        (string) ($row['subject'] ?? ''),
        (string) $row['message'],
        (string) $row['status'],
        (string) $row['source'],
        (int) $row['whatsapp_consent'] === 1 ? 'Yes' : 'No',
        (string) ($row['property_name'] ?? ''),
        (string) ($row['follow_up_date'] ?? ''),
        (string) $row['created_at'],
        (string) $row['updated_at'],
    ]);
}
fclose($out);
exit;
