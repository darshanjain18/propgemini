<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();
$pdo = getDatabaseConnection();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('news/categories.php');
    }

    $action = (string) ($_POST['action'] ?? 'save');
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT slug FROM news_categories WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && (string) $row['slug'] !== 'all-updates') {
                $pdo->prepare('UPDATE news_posts SET category_id = NULL WHERE category_id = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM news_categories WHERE id = ?')->execute([$id]);
                adminFlash('success', 'Category removed.');
            } else {
                adminFlash('error', 'That category cannot be deleted.');
            }
        }
        adminRedirect('news/categories.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $icon = trim((string) ($_POST['icon'] ?? 'fa-solid fa-newspaper'));
    $sort = (int) ($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '' || mb_strlen($name) > 80) {
        adminFlash('error', 'Category name is required.');
        adminRedirect('news/categories.php');
    }
    if ($slug === '') {
        $slug = $name;
    }
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '', '-')) ?: 'category';
    if ($icon === '') {
        $icon = 'fa-solid fa-newspaper';
    }

    if ($id > 0) {
        $pdo->prepare(
            'UPDATE news_categories SET name=?, slug=?, icon=?, sort_order=?, is_active=? WHERE id=?'
        )->execute([$name, $slug, $icon, $sort, $active, $id]);
        adminFlash('success', 'Category updated.');
    } else {
        try {
            $pdo->prepare(
                'INSERT INTO news_categories (name, slug, icon, sort_order, is_active) VALUES (?,?,?,?,?)'
            )->execute([$name, $slug, $icon, $sort, $active]);
            adminFlash('success', 'Category added.');
        } catch (Throwable $e) {
            adminFlash('error', 'Could not add category (slug may already exist).');
        }
    }
    adminRedirect('news/categories.php');
}

$categories = allNewsCategories();
$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
foreach ($categories as $cat) {
    if ((int) $cat['id'] === $editId) {
        $edit = $cat;
        break;
    }
}

adminLayoutStart('News categories', 'news');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:0;">
            <p>Manage filters shown on the public News page.</p>
            <a class="btn small secondary" href="<?= h(adminUrl('news/index.php')) ?>">Back to news</a>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin-top:0;"><?= $edit ? 'Edit category' : 'Add category' ?></h3>
        <form method="post" class="stack">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
            <div class="field">
                <label>Name</label>
                <input name="name" required maxlength="80" value="<?= h((string) ($edit['name'] ?? '')) ?>">
            </div>
            <div class="field">
                <label>Slug</label>
                <input name="slug" maxlength="100" value="<?= h((string) ($edit['slug'] ?? '')) ?>">
            </div>
            <div class="field">
                <label>Icon (Font Awesome class)</label>
                <input name="icon" maxlength="60" value="<?= h((string) ($edit['icon'] ?? 'fa-solid fa-newspaper')) ?>">
            </div>
            <div class="field">
                <label>Display order</label>
                <input name="sort_order" type="number" value="<?= (int) ($edit['sort_order'] ?? 0) ?>">
            </div>
            <div class="field">
                <label><input type="checkbox" name="is_active" value="1"<?= !isset($edit['is_active']) || (int) $edit['is_active'] === 1 ? ' checked' : '' ?>> Active</label>
            </div>
            <div class="actions-row">
                <button class="btn" type="submit">Save category</button>
                <?php if ($edit): ?>
                    <a class="btn secondary" href="<?= h(adminUrl('news/categories.php')) ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="panel table-wrap">
        <table>
            <thead>
                <tr><th>Order</th><th>Name</th><th>Slug</th><th>Icon</th><th>Active</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= (int) $cat['sort_order'] ?></td>
                    <td><?= h((string) $cat['name']) ?></td>
                    <td><?= h((string) $cat['slug']) ?></td>
                    <td><code><?= h((string) $cat['icon']) ?></code></td>
                    <td><?= (int) $cat['is_active'] === 1 ? 'Yes' : 'No' ?></td>
                    <td>
                        <a href="<?= h(adminUrl('news/categories.php?edit=' . (int) $cat['id'])) ?>">Edit</a>
                        <?php if ((string) $cat['slug'] !== 'all-updates'): ?>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                                <button class="btn small secondary" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminLayoutEnd(); ?>
