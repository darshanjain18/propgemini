<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/properties.php';

$pdo = getDatabaseConnection();
$q = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$canReorder = $q === '' && $status === '';

$sql = "SELECT p.id, p.name, p.slug, p.property_type, p.property_status, p.bhk,
               p.min_price, p.publication_status, p.is_featured, p.is_active, p.sort_order,
               c.name AS city_name, a.name AS area_name
        FROM properties p
        INNER JOIN cities c ON c.id = p.city_id
        INNER JOIN areas a ON a.id = p.area_id
        WHERE p.deleted_at IS NULL";
$params = [];

if ($q !== '') {
    $sql .= ' AND (p.name LIKE :q OR p.slug LIKE :q OR c.name LIKE :q OR a.name LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
if ($status !== '') {
    $sql .= ' AND p.publication_status = :status';
    $params['status'] = $status;
}

$sql .= $canReorder
    ? ' ORDER BY p.sort_order ASC, p.id ASC'
    : ' ORDER BY p.updated_at DESC, p.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

adminLayoutStart('Properties', 'properties');
?>
<div class="toolbar">
    <form class="filters filters-row" method="get">
        <input type="search" name="q" placeholder="Search properties" value="<?= h($q) ?>">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['Draft', 'Published', 'Unpublished'] as $option): ?>
                <option value="<?= h($option) ?>"<?= $status === $option ? ' selected' : '' ?>><?= h($option) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn secondary" type="submit">Filter</button>
    </form>
    <a class="btn" href="<?= h(adminUrl('properties/edit')) ?>">Add property</a>
</div>

<?php if ($canReorder && $rows !== []): ?>
    <p class="help" style="margin:0 0 10px;" id="propertyOrderStatus">Drag rows to rearrange how listings appear on the website.</p>
<?php elseif (!$canReorder): ?>
    <p class="help" style="margin:0 0 10px;">Clear search/filters to drag and rearrange listing order.</p>
<?php endif; ?>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <?php if ($canReorder): ?><th class="col-drag"></th><?php endif; ?>
            <th>Property</th>
            <th>Location</th>
            <th>Type</th>
            <th>Price</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody
            <?php if ($canReorder && $rows !== []): ?>
                data-sortable-properties
                data-reorder-url="<?= h(adminUrl('properties/reorder')) ?>"
                data-csrf="<?= h(csrfToken()) ?>"
            <?php endif; ?>
        >
        <?php if ($rows === []): ?>
            <tr><td colspan="<?= $canReorder ? 7 : 6 ?>" class="muted">No properties found.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr<?= $canReorder ? ' draggable="true" data-property-id="' . (int) $row['id'] . '"' : '' ?>>
                    <?php if ($canReorder): ?>
                        <td class="col-drag"><span class="row-drag-handle" title="Drag to reorder">⠿</span></td>
                    <?php endif; ?>
                    <td>
                        <strong><?= h((string) $row['name']) ?></strong>
                        <div class="muted"><?= h((string) $row['slug']) ?><?= (int) $row['is_featured'] ? ' · Featured' : '' ?></div>
                    </td>
                    <td><?= h((string) $row['area_name']) ?>, <?= h((string) $row['city_name']) ?></td>
                    <td><?= h((string) ($row['property_type'] ?? '')) ?><div class="muted"><?= h((string) ($row['bhk'] ?? '')) ?></div></td>
                    <td><?= h(formatPriceLabel($row['min_price'] !== null ? (float) $row['min_price'] : null)) ?></td>
                    <td><span class="badge <?= h(strtolower((string) $row['publication_status'])) ?>"><?= h((string) $row['publication_status']) ?></span></td>
                    <td class="actions-row">
                        <a class="btn small secondary" href="<?= h(adminUrl('properties/edit?id=' . (int) $row['id'])) ?>">Edit</a>
                        <a class="btn small secondary" href="<?= h(propertyUrl((string) $row['slug'])) ?>" target="_blank" rel="noopener">View</a>
                        <form method="post" action="<?= h(adminUrl('properties/delete')) ?>" onsubmit="return confirm('Remove <?= h((string) $row['name']) ?> from the site? It will be hidden, not permanently deleted.');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button class="btn small danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
adminLayoutEnd();
