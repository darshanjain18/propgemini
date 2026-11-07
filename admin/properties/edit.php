<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_once dirname(__DIR__) . '/includes/amenities.php';
require_once dirname(__DIR__) . '/includes/publish.php';
require_once dirname(__DIR__, 2) . '/includes/properties.php';

ensurePropertyColumns();
$pdo = getDatabaseConnection();
$id = (int) ($_GET['id'] ?? 0);
$property = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'city_id' => '',
    'area_id' => '',
    'property_type' => 'Apartment',
    'property_status' => 'New Launch',
    'bhk' => '',
    'min_price' => null,
    'max_price' => null,
    'address' => '',
    'overview' => '',
    'possession_date' => '',
    'map_url' => '',
    'is_featured' => 0,
    'is_active' => 1,
    'publication_status' => 'Draft',
    'construction_status' => '',
    'construction_progress' => '',
    'launch_date' => '',
    'builtup_min_sqft' => '',
    'builtup_max_sqft' => '',
    'about_builder' => '',
    'seo_title' => '',
    'seo_description' => '',
];
$images = [];
$videos = [];
$selectedAmenities = [];
$unitTypes = [];
$brochure = null;

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row === false) {
        adminFlash('error', 'Property not found.');
        adminRedirect('properties/index.php');
    }
    $property = $row;

    $images = $pdo->prepare('SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order, id');
    $images->execute([$id]);
    $images = $images->fetchAll();

    $videos = $pdo->prepare('SELECT * FROM property_videos WHERE property_id = ? ORDER BY sort_order, id');
    $videos->execute([$id]);
    $videos = $videos->fetchAll();

    $amenitiesStmt = $pdo->prepare('SELECT amenity_name FROM property_amenities WHERE property_id = ? ORDER BY id');
    $amenitiesStmt->execute([$id]);
    $selectedAmenities = $amenitiesStmt->fetchAll(PDO::FETCH_COLUMN);

    $unitTypes = $pdo->prepare('SELECT * FROM property_unit_types WHERE property_id = ? ORDER BY sort_order, id');
    $unitTypes->execute([$id]);
    $unitTypes = $unitTypes->fetchAll();

    $brochureStmt = $pdo->prepare('SELECT * FROM property_brochures WHERE property_id = ? ORDER BY id DESC LIMIT 1');
    $brochureStmt->execute([$id]);
    $brochure = $brochureStmt->fetch() ?: null;
}

$cities = $pdo->query('SELECT id, name FROM cities ORDER BY name')->fetchAll();
$areas = [];
if (!empty($property['city_id'])) {
    $areaStmt = $pdo->prepare('SELECT id, name FROM areas WHERE city_id = ? ORDER BY name');
    $areaStmt->execute([(int) $property['city_id']]);
    $areas = $areaStmt->fetchAll();
}

$publishChecklist = propertyPublishChecklist(
    (string) ($property['name'] ?? ''),
    (int) ($property['city_id'] ?? 0),
    (int) ($property['area_id'] ?? 0),
    $property['min_price'] !== null && $property['min_price'] !== '' ? (float) $property['min_price'] : null,
    count($images)
);

$typeOptions = $pdo->query(
    "SELECT DISTINCT property_type FROM properties
     WHERE deleted_at IS NULL
       AND property_type IS NOT NULL AND property_type <> ''
     ORDER BY property_type"
)->fetchAll(PDO::FETCH_COLUMN);
foreach (['Apartment', 'Villa', 'Penthouse', 'Plot', 'Commercial', 'Office', 'Studio'] as $defaultType) {
    if (!in_array($defaultType, $typeOptions, true)) {
        $typeOptions[] = $defaultType;
    }
}
sort($typeOptions);

$selectedAmenityMap = array_fill_keys(array_map('strval', $selectedAmenities), true);
$cityName = '';
foreach ($cities as $city) {
    if ((int) $city['id'] === (int) $property['city_id']) {
        $cityName = (string) $city['name'];
        break;
    }
}
$areaName = '';
foreach ($areas as $area) {
    if ((int) $area['id'] === (int) $property['area_id']) {
        $areaName = (string) $area['name'];
        break;
    }
}

adminLayoutStart($id > 0 ? 'Edit property' : 'Add property', 'properties');
?>
<form class="stack" method="post" action="<?= h(adminUrl('properties/save.php')) ?>" enctype="multipart/form-data" id="propertyForm">
    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="id" value="<?= (int) $property['id'] ?>">

    <div class="panel publish-checklist-panel">
        <div class="toolbar" style="margin-bottom:10px;">
            <h3 style="margin:0;">Publish checklist</h3>
            <span class="badge <?= $publishChecklist['ready'] ? 'published' : 'draft' ?>">
                <?= $publishChecklist['ready'] ? 'Ready to publish' : 'Not ready' ?>
            </span>
        </div>
        <ul class="publish-checklist">
            <?php foreach ($publishChecklist['checks'] as $check): ?>
                <li class="<?= $check['ok'] ? 'is-ok' : 'is-missing' ?>">
                    <strong><?= $check['ok'] ? '✓' : '○' ?> <?= h($check['label']) ?></strong>
                    <?php if (!$check['ok']): ?>
                        <span class="muted"><?= h($check['hint']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="help" style="margin:10px 0 0;">Choosing Published without these items will save the listing as Draft.</p>
    </div>

    <div class="panel">
        <h3>Basics</h3>
        <div class="form-grid">
            <div class="field full">
                <label for="name">Name</label>
                <input id="name" name="name" required value="<?= h((string) $property['name']) ?>">
            </div>
            <div class="field">
                <label for="slug">Slug</label>
                <input id="slug" name="slug" value="<?= h((string) ($property['slug'] ?? '')) ?>" placeholder="auto from name">
            </div>
            <div class="field">
                <label for="publication_status">Publication</label>
                <select id="publication_status" name="publication_status">
                    <?php foreach (['Draft', 'Published', 'Unpublished'] as $status): ?>
                        <option value="<?= h($status) ?>"<?= ((string) $property['publication_status'] === $status) ? ' selected' : '' ?>><?= h($status) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="help">Publish requires name, location, price, and at least one image.</p>
            </div>

            <div class="field">
                <label>City</label>
                <div class="search-select" data-search-select>
                    <input type="text" class="search-input" placeholder="Search city..." value="<?= h($cityName) ?>" autocomplete="off">
                    <input type="hidden" name="city_id" id="city_id" value="<?= h((string) $property['city_id']) ?>" required>
                    <div class="list" role="listbox">
                        <?php foreach ($cities as $city): ?>
                            <button type="button" data-value="<?= (int) $city['id'] ?>" data-label="<?= h((string) $city['name']) ?>"><?= h((string) $city['name']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="field">
                <label>Area</label>
                <div class="search-select" data-search-select data-areas-target>
                    <input type="text" class="search-input" id="areaSearchInput" placeholder="Search area..." value="<?= h($areaName) ?>" autocomplete="off" <?= empty($property['city_id']) ? 'disabled' : '' ?>>
                    <input type="hidden" name="area_id" id="area_id" value="<?= h((string) $property['area_id']) ?>" required data-areas-url="<?= h(adminUrl('areas.php')) ?>" data-selected="<?= h((string) $property['area_id']) ?>">
                    <div class="list" role="listbox" id="areaList">
                        <?php foreach ($areas as $area): ?>
                            <button type="button" data-value="<?= (int) $area['id'] ?>" data-label="<?= h((string) $area['name']) ?>"><?= h((string) $area['name']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="field">
                <label>Property type</label>
                <div class="combo" data-combo>
                    <div class="combo-row">
                        <input type="text" class="combo-input" placeholder="Search existing types..." value="<?= h((string) ($property['property_type'] ?? '')) ?>" autocomplete="off">
                        <button type="button" class="btn secondary small" data-quick-create title="Add a new property type">+ Create</button>
                    </div>
                    <input type="hidden" name="property_type" id="property_type" value="<?= h((string) ($property['property_type'] ?? '')) ?>">
                    <div class="combo-create-panel" data-create-panel>
                        <input type="text" class="combo-create-input" placeholder="Enter new type name (e.g. Duplex)" maxlength="100">
                        <button type="button" class="btn small" data-confirm-create>Add type</button>
                    </div>
                    <div class="combo-create-feedback" data-create-feedback>Type added and selected.</div>
                    <div class="list">
                        <?php foreach ($typeOptions as $type): ?>
                            <button type="button" data-value="<?= h((string) $type) ?>"><?= h((string) $type) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <p class="help">Search an existing type, or click <strong>+ Create</strong> to add a new one.</p>
            </div>

            <div class="field">
                <label for="property_status">Status</label>
                <select id="property_status" name="property_status">
                    <?php foreach (['New Launch', 'Under Construction', 'Ready'] as $status): ?>
                        <option value="<?= h($status) ?>"<?= ((string) $property['property_status'] === $status) ? ' selected' : '' ?>><?= h($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="bhk">Configuration</label>
                <input id="bhk" name="bhk" value="<?= h((string) ($property['bhk'] ?? '')) ?>" placeholder="3 BHK">
            </div>
            <div class="field">
                <label for="min_price">Starting price (INR)</label>
                <input id="min_price" name="min_price" value="<?= h(formatRupeesInput($property['min_price'] !== null ? (float) $property['min_price'] : null)) ?>" placeholder="16500000">
            </div>
            <div class="field">
                <label for="max_price">Max price (INR)</label>
                <input id="max_price" name="max_price" value="<?= h(formatRupeesInput($property['max_price'] !== null ? (float) $property['max_price'] : null)) ?>">
            </div>
            <div class="field full">
                <label for="address">Address</label>
                <input id="address" name="address" value="<?= h((string) ($property['address'] ?? '')) ?>">
            </div>
            <div class="field full">
                <label for="overview">Overview</label>
                <textarea id="overview" name="overview"><?= h((string) ($property['overview'] ?? '')) ?></textarea>
            </div>
            <div class="field full checks">
                <label><input type="checkbox" name="is_featured" value="1"<?= (int) $property['is_featured'] ? ' checked' : '' ?>> Featured on home</label>
                <label><input type="checkbox" name="is_active" value="1"<?= (int) $property['is_active'] ? ' checked' : '' ?>> Active</label>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3>RERA</h3>
        <div class="form-grid">
            <div class="field">
                <label for="rera_number">RERA number</label>
                <input id="rera_number" name="rera_number" value="<?= h((string) ($property['rera_number'] ?? '')) ?>" placeholder="P521000XXXXX">
            </div>
            <div class="field">
                <label for="rera_expiry">RERA expiry</label>
                <input id="rera_expiry" name="rera_expiry" type="date" value="<?= h((string) ($property['rera_expiry'] ?? '')) ?>">
            </div>
        </div>
    </div>

    <div class="panel">
        <h3>Construction & location</h3>
        <div class="form-grid">
            <div class="field">
                <label for="construction_status">Construction status</label>
                <?php
                $constructionValue = (string) ($property['construction_status'] ?? '');
                $constructionOptions = ['Pending', 'Under Construction', 'Ready'];
                if ($constructionValue !== '' && !in_array($constructionValue, $constructionOptions, true)) {
                    array_unshift($constructionOptions, $constructionValue);
                }
                ?>
                <select id="construction_status" name="construction_status">
                    <option value="">Select status</option>
                    <?php foreach ($constructionOptions as $option): ?>
                        <option value="<?= h($option) ?>"<?= $constructionValue === $option ? ' selected' : '' ?>><?= h($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" id="launchDateField">
                <label for="launch_date">Launch date</label>
                <input id="launch_date" name="launch_date" type="date" value="<?= h((string) ($property['launch_date'] ?? '')) ?>">
                <p class="help">Shown on the site when construction status is Pending.</p>
            </div>
            <div class="field">
                <label for="construction_progress">Progress %</label>
                <input id="construction_progress" name="construction_progress" type="number" min="0" max="100" value="<?= h((string) ($property['construction_progress'] ?? '')) ?>">
            </div>
            <div class="field">
                <label for="possession_date">Possession date</label>
                <input id="possession_date" name="possession_date" type="date" value="<?= h((string) ($property['possession_date'] ?? '')) ?>">
            </div>
            <div class="field full">
                <label for="map_url">Google Maps link</label>
                <input id="map_url" name="map_url" type="url" placeholder="https://maps.google.com/..." value="<?= h((string) ($property['map_url'] ?? '')) ?>">
                <p class="help">Paste a Google Maps share link for this property. Latitude/longitude fields have been removed.</p>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3>SEO</h3>
        <div class="form-grid">
            <div class="field full">
                <label for="seo_title">Meta title</label>
                <input id="seo_title" name="seo_title" value="<?= h((string) ($property['seo_title'] ?? '')) ?>">
            </div>
            <div class="field full">
                <label for="seo_description">Meta description</label>
                <textarea id="seo_description" name="seo_description"><?= h((string) ($property['seo_description'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3>Amenities</h3>
        <div class="amenity-board">
            <?php foreach (amenityCatalog() as $group => $items): ?>
                <div class="amenity-group">
                    <h4><?= h($group) ?></h4>
                    <div class="amenity-options">
                        <?php foreach ($items as $item): ?>
                            <label>
                                <input type="checkbox" name="amenities[]" value="<?= h($item) ?>"<?= isset($selectedAmenityMap[$item]) ? ' checked' : '' ?>>
                                <span><?= h($item) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel" id="floor-plans">
        <h3>Floor plans &amp; built-up area</h3>
        <p class="help">These fields are saved with the property and shown on the Floor plans tab. Built-up area appears beside the heading, for example 1,000–1,500 sq. ft.</p>
        <div class="form-grid">
            <div class="field">
                <label for="builtup_min_sqft">Built-up area from (sq.ft)</label>
                <input id="builtup_min_sqft" name="builtup_min_sqft" inputmode="decimal" value="<?= h((string) ($property['builtup_min_sqft'] ?? '')) ?>" placeholder="1000">
            </div>
            <div class="field">
                <label for="builtup_max_sqft">Built-up area to (sq.ft)</label>
                <input id="builtup_max_sqft" name="builtup_max_sqft" inputmode="decimal" value="<?= h((string) ($property['builtup_max_sqft'] ?? '')) ?>" placeholder="1500">
            </div>
        </div>
        <div id="floorPlanRows" style="display:grid;gap:16px;margin-top:16px;">
            <?php
            $floorRows = $unitTypes !== [] ? $unitTypes : [[
                'id' => 0,
                'configuration' => (string) ($property['bhk'] ?? ''),
                'carpet_area_sqft' => '',
                'price' => null,
                'floor_plan_path' => '',
            ]];
            foreach ($floorRows as $index => $unit):
                $planPath = trim((string) ($unit['floor_plan_path'] ?? ''));
            ?>
                <div class="floor-plan-row" data-floor-row style="padding:14px;border:1px solid #e4d7c4;border-radius:12px;background:#fffdf8;">
                    <input type="hidden" name="unit_id[<?= (int) $index ?>]" value="<?= (int) ($unit['id'] ?? 0) ?>">
                    <div class="form-grid">
                        <div class="field">
                            <label>Configuration</label>
                            <input name="unit_configuration[<?= (int) $index ?>]" value="<?= h((string) ($unit['configuration'] ?? '')) ?>" placeholder="3 BHK">
                        </div>
                        <div class="field">
                            <label>Carpet area (sq.ft)</label>
                            <input name="unit_carpet[<?= (int) $index ?>]" inputmode="decimal" value="<?= h((string) ($unit['carpet_area_sqft'] ?? '')) ?>">
                        </div>
                        <div class="field">
                            <label>Unit price (INR)</label>
                            <input name="unit_price[<?= (int) $index ?>]" value="<?= h(formatRupeesInput(isset($unit['price']) && $unit['price'] !== null && $unit['price'] !== '' ? (float) $unit['price'] : null)) ?>">
                        </div>
                        <div class="field">
                            <label>Floor plan image or PDF</label>
                            <input name="floor_plans[<?= (int) $index ?>]" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf">
                            <?php if ($planPath !== ''): ?>
                                <p class="help">Current file: <a href="<?= h(webPath($planPath)) ?>" target="_blank" rel="noopener"><?= h(basename($planPath)) ?></a></p>
                                <?php if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $planPath) === 1): ?>
                                    <img src="<?= h(webPath($planPath)) ?>" alt="" style="display:block;width:180px;height:120px;object-fit:cover;border-radius:8px;margin-top:8px;">
                                <?php endif; ?>
                                <label class="help" style="display:flex;gap:8px;align-items:center;margin-top:8px;">
                                    <input type="checkbox" name="clear_floor_plan[<?= (int) $index ?>]" value="1">
                                    Remove this floor plan file
                                </label>
                            <?php endif; ?>
                            <?php if ((int) ($unit['id'] ?? 0) > 0): ?>
                                <label class="help" style="display:flex;gap:8px;align-items:center;margin-top:8px;">
                                    <input type="checkbox" name="unit_remove[<?= (int) $index ?>]" value="1">
                                    Delete this floor plan row
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn small secondary" type="button" id="addFloorPlan" style="margin-top:12px;">Add another floor plan</button>
    </div>

    <div class="panel">
        <h3>Media uploads</h3>
        <div class="form-grid">
            <div class="field">
                <label>Property images</label>
                <div class="dropzone" data-dropzone data-accept="image/*">
                    <strong>Drag & drop images</strong>
                    <span>or click to browse · auto-resized on upload (max 1920px)</span>
                    <input id="images" name="images[]" type="file" accept="image/*" multiple>
                    <div class="file-chips" data-file-chips></div>
                </div>
            </div>
            <div class="field">
                <label>Walkthrough video</label>
                <div class="dropzone" data-dropzone data-accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov">
                    <strong>Drag & drop video</strong>
                    <span>or click to browse · MP4 / WEBM</span>
                    <input id="video_file" name="video_file" type="file" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov">
                    <div class="file-chips" data-file-chips></div>
                </div>
                <div class="field" style="margin-top:12px;">
                    <label for="video_url">Or video URL / existing path</label>
                    <input id="video_url" name="video_url" value="<?= h((string) ($videos[0]['video_path'] ?? '')) ?>" placeholder="public/videos/... or https://...">
                </div>
            </div>
            <div class="field">
                <label>Brochure PDF</label>
                <div class="dropzone" data-dropzone data-accept="application/pdf,.pdf">
                    <strong>Drag & drop brochure</strong>
                    <span>or click to browse · PDF only</span>
                    <input id="brochure" name="brochure" type="file" accept="application/pdf,.pdf">
                    <div class="file-chips" data-file-chips></div>
                </div>
                <?php if ($brochure): ?>
                    <p class="help">Current: <a href="<?= h(webPath((string) $brochure['brochure_path'])) ?>" target="_blank" rel="noopener"><?= h((string) $brochure['brochure_path']) ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="panel" id="about-builder">
        <h3>About the builder</h3>
        <div class="field full">
            <label for="about_builder">About the builder</label>
            <textarea id="about_builder" name="about_builder" rows="6" placeholder="Who is building this project, and what should a buyer know?"><?= h((string) ($property['about_builder'] ?? '')) ?></textarea>
            <p class="help">Appears on the property page after Images / Videos.</p>
        </div>
    </div>

    <div class="actions-row">
        <button class="btn" type="submit">Save property</button>
        <a class="btn secondary" href="<?= h(adminUrl('properties/index.php')) ?>">Cancel</a>
        <?php if ($id > 0): ?>
            <button class="btn danger" type="submit" formaction="<?= h(adminUrl('properties/delete.php')) ?>" onclick="return confirm('Delete this property permanently?');">Delete</button>
        <?php endif; ?>
    </div>
</form>

<?php if ($images !== []): ?>
    <div class="panel" style="margin-top:16px;">
        <div class="toolbar" style="margin-bottom:8px;">
            <h3 style="margin:0;">Current gallery</h3>
            <span class="muted" id="galleryOrderStatus">Drag images to reorder · first image is primary</span>
        </div>
        <div
            class="media-grid"
            data-sortable-gallery
            data-property-id="<?= (int) $property['id'] ?>"
            data-reorder-url="<?= h(adminUrl('properties/media_reorder')) ?>"
            data-csrf="<?= h(csrfToken()) ?>"
        >
            <?php foreach ($images as $index => $image): ?>
                <div class="media-item" draggable="true" data-media-id="<?= (int) $image['id'] ?>">
                    <div class="media-drag-handle" title="Drag to reorder">⠿</div>
                    <img src="<?= h(webPath((string) $image['image_path'])) ?>" alt="">
                    <div class="meta">
                        <span data-order-label>Order <?= $index + 1 ?></span>
                        <?php if ($index === 0): ?><em>Primary</em><?php endif; ?>
                    </div>
                    <form method="post" action="<?= h(adminUrl('properties/media_delete.php')) ?>" onsubmit="return confirm('Remove this image?');">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="type" value="image">
                        <input type="hidden" name="media_id" value="<?= (int) $image['id'] ?>">
                        <input type="hidden" name="property_id" value="<?= (int) $property['id'] ?>">
                        <button class="btn small danger" type="submit">×</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<script>
const constructionStatus = document.getElementById('construction_status');
const launchDateField = document.getElementById('launchDateField');
const syncLaunchDate = () => {
    if (!constructionStatus || !launchDateField) return;
    const pending = constructionStatus.value.toLowerCase() === 'pending';
    launchDateField.hidden = !pending;
};
constructionStatus?.addEventListener('change', syncLaunchDate);
syncLaunchDate();

const floorPlanRows = document.getElementById('floorPlanRows');
document.getElementById('addFloorPlan')?.addEventListener('click', () => {
    if (!floorPlanRows) return;
    const index = floorPlanRows.querySelectorAll('[data-floor-row]').length;
    const row = document.createElement('div');
    row.className = 'floor-plan-row';
    row.setAttribute('data-floor-row', '');
    row.style.cssText = 'padding:14px;border:1px solid #e4d7c4;border-radius:12px;background:#fffdf8;';
    row.innerHTML = `
        <input type="hidden" name="unit_id[${index}]" value="0">
        <div class="form-grid">
            <div class="field">
                <label>Configuration</label>
                <input name="unit_configuration[${index}]" placeholder="3 BHK">
            </div>
            <div class="field">
                <label>Carpet area (sq.ft)</label>
                <input name="unit_carpet[${index}]" inputmode="decimal">
            </div>
            <div class="field">
                <label>Unit price (INR)</label>
                <input name="unit_price[${index}]">
            </div>
            <div class="field">
                <label>Floor plan image or PDF</label>
                <input name="floor_plans[${index}]" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf">
            </div>
        </div>`;
    floorPlanRows.appendChild(row);
});
</script>
<?php adminLayoutEnd(); ?>
