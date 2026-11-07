<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_once dirname(__DIR__) . '/includes/publish.php';
require_once dirname(__DIR__, 2) . '/includes/admin_security.php';
require_once dirname(__DIR__, 2) . '/includes/automations.php';
require_once dirname(__DIR__, 2) . '/includes/properties.php';

requireAdmin();
ensurePropertyColumns();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('properties/index.php');
}

$pdo = getDatabaseConnection();
$id = (int) ($_POST['id'] ?? 0);

$name = trim((string) ($_POST['name'] ?? ''));
$slugInput = trim((string) ($_POST['slug'] ?? ''));
$cityId = (int) ($_POST['city_id'] ?? 0);
$areaId = (int) ($_POST['area_id'] ?? 0);
$publication = (string) ($_POST['publication_status'] ?? 'Draft');
if (!in_array($publication, ['Draft', 'Published', 'Unpublished'], true)) {
    $publication = 'Draft';
}

$minPrice = parseMoneyToRupees((string) ($_POST['min_price'] ?? ''));
$maxPrice = parseMoneyToRupees((string) ($_POST['max_price'] ?? ''));

if ($name === '' || $cityId <= 0 || $areaId <= 0) {
    adminFlash('error', 'Name, city and area are required.');
    adminRedirect($id > 0 ? 'properties/edit.php?id=' . $id : 'properties/edit.php');
}

$areaCheck = $pdo->prepare('SELECT id FROM areas WHERE id = ? AND city_id = ? LIMIT 1');
$areaCheck->execute([$areaId, $cityId]);
if ($areaCheck->fetchColumn() === false) {
    adminFlash('error', 'Selected area does not belong to the city.');
    adminRedirect($id > 0 ? 'properties/edit.php?id=' . $id : 'properties/edit.php');
}

$slug = uniquePropertySlug($pdo, $slugInput !== '' ? $slugInput : $name, $id > 0 ? $id : null);

$progressRaw = trim((string) ($_POST['construction_progress'] ?? ''));
$progress = $progressRaw === '' ? null : max(0, min(100, (int) $progressRaw));
$possession = trim((string) ($_POST['possession_date'] ?? ''));
$mapUrl = trim((string) ($_POST['map_url'] ?? ''));
if ($mapUrl !== '' && !preg_match('#^https?://#i', $mapUrl)) {
    adminFlash('error', 'Google Maps link must start with http:// or https://');
    adminRedirect($id > 0 ? 'properties/edit.php?id=' . $id : 'properties/edit.php');
}

$reraNumber = trim((string) ($_POST['rera_number'] ?? '')) ?: null;
$reraExpiry = trim((string) ($_POST['rera_expiry'] ?? ''));
$isActive = isset($_POST['is_active']) ? 1 : 0;
$constructionStatus = trim((string) ($_POST['construction_status'] ?? ''));
$launchDate = trim((string) ($_POST['launch_date'] ?? ''));
$builtupMin = trim((string) ($_POST['builtup_min_sqft'] ?? ''));
$builtupMax = trim((string) ($_POST['builtup_max_sqft'] ?? ''));

$payload = [
    'name' => $name,
    'slug' => $slug,
    'city_id' => $cityId,
    'area_id' => $areaId,
    'property_type' => trim((string) ($_POST['property_type'] ?? '')) ?: null,
    'property_status' => trim((string) ($_POST['property_status'] ?? '')) ?: null,
    'bhk' => trim((string) ($_POST['bhk'] ?? '')) ?: null,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'address' => trim((string) ($_POST['address'] ?? '')) ?: null,
    'overview' => trim((string) ($_POST['overview'] ?? '')) ?: null,
    'possession_date' => $possession !== '' ? $possession : null,
    'map_url' => $mapUrl !== '' ? $mapUrl : null,
    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
    'is_active' => $isActive,
    'publication_status' => $publication,
    'construction_status' => $constructionStatus !== '' ? $constructionStatus : null,
    'construction_progress' => $progress,
    'launch_date' => strcasecmp($constructionStatus, 'Pending') === 0 && $launchDate !== '' ? $launchDate : null,
    'builtup_min_sqft' => $builtupMin !== '' ? (float) $builtupMin : null,
    'builtup_max_sqft' => $builtupMax !== '' ? (float) $builtupMax : null,
    'about_builder' => trim((string) ($_POST['about_builder'] ?? '')) ?: null,
    'seo_title' => trim((string) ($_POST['seo_title'] ?? '')) ?: null,
    'seo_description' => trim((string) ($_POST['seo_description'] ?? '')) ?: null,
    'rera_number' => $reraNumber,
    'rera_expiry' => $reraExpiry !== '' ? $reraExpiry : null,
];

try {
    $pdo->beginTransaction();

    $before = null;
    $wasPublished = false;
    if ($id > 0) {
        $beforeStmt = $pdo->prepare('SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $beforeStmt->execute([$id]);
        $before = $beforeStmt->fetch() ?: null;
        if ($before === null) {
            throw new RuntimeException('Property not found.');
        }
        $wasPublished = ((string) ($before['publication_status'] ?? '')) === 'Published';

        $stmt = $pdo->prepare(
            'UPDATE properties SET
                name=:name, slug=:slug, city_id=:city_id, area_id=:area_id,
                property_type=:property_type, property_status=:property_status, bhk=:bhk,
                min_price=:min_price, max_price=:max_price, address=:address, overview=:overview,
                possession_date=:possession_date, map_url=:map_url,
                is_featured=:is_featured, is_active=:is_active, publication_status=:publication_status,
                construction_status=:construction_status, construction_progress=:construction_progress,
                launch_date=:launch_date, builtup_min_sqft=:builtup_min_sqft, builtup_max_sqft=:builtup_max_sqft,
                about_builder=:about_builder,
                seo_title=:seo_title, seo_description=:seo_description,
                rera_number=:rera_number, rera_expiry=:rera_expiry
             WHERE id=:id'
        );
        $stmt->execute($payload + ['id' => $id]);
    } else {
        $nextSort = (int) $pdo->query(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM properties WHERE deleted_at IS NULL'
        )->fetchColumn();
        $stmt = $pdo->prepare(
            'INSERT INTO properties
                (name, slug, city_id, area_id, property_type, property_status, bhk, min_price, max_price,
                 address, overview, possession_date, map_url, is_featured, is_active, sort_order,
                 publication_status, construction_status, construction_progress, launch_date,
                 builtup_min_sqft, builtup_max_sqft, about_builder, seo_title, seo_description,
                 rera_number, rera_expiry)
             VALUES
                (:name, :slug, :city_id, :area_id, :property_type, :property_status, :bhk, :min_price, :max_price,
                 :address, :overview, :possession_date, :map_url, :is_featured, :is_active, :sort_order,
                 :publication_status, :construction_status, :construction_progress, :launch_date,
                 :builtup_min_sqft, :builtup_max_sqft, :about_builder, :seo_title, :seo_description,
                 :rera_number, :rera_expiry)'
        );
        $stmt->execute($payload + ['sort_order' => $nextSort]);
        $id = (int) $pdo->lastInsertId();
    }

    // Amenities from selectable checklist
    $pdo->prepare('DELETE FROM property_amenities WHERE property_id = ?')->execute([$id]);
    $amenityInsert = $pdo->prepare('INSERT INTO property_amenities (property_id, amenity_name) VALUES (?, ?)');
    $amenityNames = [];
    $postedAmenities = $_POST['amenities'] ?? [];
    if (!is_array($postedAmenities)) {
        $postedAmenities = [];
    }
    foreach ($postedAmenities as $line) {
        $line = trim((string) $line);
        if ($line === '' || isset($amenityNames[$line])) {
            continue;
        }
        $amenityInsert->execute([$id, $line]);
        $amenityNames[$line] = $line;
    }
    $pdo->prepare('UPDATE properties SET amenities = ? WHERE id = ?')->execute([
        $amenityNames === [] ? null : implode(', ', array_values($amenityNames)),
        $id,
    ]);
    // Images
    if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $sortStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM property_images WHERE property_id = ?');
        $sortStmt->execute([$id]);
        $sort = (int) $sortStmt->fetchColumn();
        $imageInsert = $pdo->prepare(
            'INSERT INTO property_images (property_id, image_path, sort_order, media_type) VALUES (?, ?, ?, \'image\')'
        );

        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            ];
            $path = storeImageUpload($file);
            $sort++;
            $imageInsert->execute([$id, $path, $sort]);
        }
    }

    // Video file upload wins over URL/path when provided
    $videoUrl = trim((string) ($_POST['video_url'] ?? ''));
    if (!empty($_FILES['video_file']) && ($_FILES['video_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $videoUrl = storeVideoUpload($_FILES['video_file']);
    }
    $pdo->prepare('DELETE FROM property_videos WHERE property_id = ?')->execute([$id]);
    if ($videoUrl !== '') {
        $pdo->prepare(
            'INSERT INTO property_videos (property_id, video_path, sort_order) VALUES (?, ?, 0)'
        )->execute([$id, $videoUrl]);
    }

    // Brochure
    if (!empty($_FILES['brochure']) && ($_FILES['brochure']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $brochurePath = storeBrochureUpload($_FILES['brochure']);
        $pdo->prepare('DELETE FROM property_brochures WHERE property_id = ?')->execute([$id]);
        $pdo->prepare(
            'INSERT INTO property_brochures (property_id, brochure_path) VALUES (?, ?)'
        )->execute([$id, $brochurePath]);
    }

    $unitConfigs = $_POST['unit_configuration'] ?? [];
    $unitIds = $_POST['unit_id'] ?? [];
    $unitCarpets = $_POST['unit_carpet'] ?? [];
    $unitPrices = $_POST['unit_price'] ?? [];
    if (!is_array($unitConfigs)) {
        $unitConfigs = [$unitConfigs];
        $unitIds = [$unitIds];
        $unitCarpets = [$unitCarpets];
        $unitPrices = [$unitPrices];
    }

    $updateUnit = $pdo->prepare(
        'UPDATE property_unit_types
         SET configuration = ?, carpet_area_sqft = ?, price = ?, floor_plan_path = ?, sort_order = ?
         WHERE id = ? AND property_id = ?'
    );
    $insertUnit = $pdo->prepare(
        'INSERT INTO property_unit_types
            (property_id, configuration, carpet_area_sqft, price, floor_plan_path, is_available, sort_order)
         VALUES (?, ?, ?, ?, ?, 1, ?)'
    );
    $findUnit = $pdo->prepare('SELECT floor_plan_path FROM property_unit_types WHERE id = ? AND property_id = ? LIMIT 1');
    $deleteUnit = $pdo->prepare('DELETE FROM property_unit_types WHERE id = ? AND property_id = ?');

    foreach (array_values($unitConfigs) as $index => $configRaw) {
        $unitId = (int) ($unitIds[$index] ?? 0);
        $config = trim((string) $configRaw);
        $carpet = trim((string) ($unitCarpets[$index] ?? ''));
        $price = parseMoneyToRupees((string) ($unitPrices[$index] ?? ''));
        $remove = (string) ($_POST['unit_remove'][$index] ?? '') === '1';
        $clearPlan = (string) ($_POST['clear_floor_plan'][$index] ?? '') === '1';
        $newPath = null;
        $fileError = (int) ($_FILES['floor_plans']['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($fileError !== UPLOAD_ERR_NO_FILE) {
            $newPath = storeFloorPlanUpload([
                'name' => $_FILES['floor_plans']['name'][$index] ?? '',
                'type' => $_FILES['floor_plans']['type'][$index] ?? '',
                'tmp_name' => $_FILES['floor_plans']['tmp_name'][$index] ?? '',
                'error' => $fileError,
                'size' => $_FILES['floor_plans']['size'][$index] ?? 0,
            ]);
        }

        if ($remove) {
            if ($unitId > 0) {
                $deleteUnit->execute([$unitId, $id]);
            }
            continue;
        }

        if ($config === '' && $carpet === '' && $price === null && $newPath === null && $unitId === 0) {
            continue;
        }

        $existingPath = null;
        if ($unitId > 0) {
            $findUnit->execute([$unitId, $id]);
            $found = $findUnit->fetchColumn();
            if ($found === false) {
                $unitId = 0;
            } else {
                $existingPath = $found !== null && $found !== '' ? (string) $found : null;
            }
        }
        $finalPath = $newPath ?? ($clearPlan ? null : $existingPath);
        $label = $config !== '' ? $config : (string) ($payload['bhk'] ?? 'Floor plan');
        $carpetValue = $carpet !== '' ? (float) $carpet : null;

        if ($unitId > 0) {
            $updateUnit->execute([$label, $carpetValue, $price, $finalPath, $index, $unitId, $id]);
        } else {
            $insertUnit->execute([$id, $label, $carpetValue, $price, $finalPath, $index]);
        }
    }

    // Publish validation: name, location, price, ≥1 image
    if ($publication === 'Published') {
        $imgStmt = $pdo->prepare('SELECT COUNT(*) FROM property_images WHERE property_id = ?');
        $imgStmt->execute([$id]);
        $imageCount = (int) $imgStmt->fetchColumn();
        $checklist = propertyPublishChecklist($name, $cityId, $areaId, $minPrice, $imageCount);

        if (!$checklist['ready']) {
            $pdo->prepare("UPDATE properties SET publication_status = 'Draft' WHERE id = ?")->execute([$id]);
            $pdo->commit();
            $missing = propertyPublishMissingLabels($checklist);
            logAdminActivity(
                'property_save_draft_forced',
                'property',
                $id,
                'Publish blocked: ' . implode(', ', $missing)
            );
            adminFlash(
                'error',
                'Saved as Draft. To publish, complete: ' . implode(', ', $missing) . '.'
            );
            adminRedirect('properties/edit.php?id=' . $id);
        }
    }

    $pdo->commit();

    if ($before !== null) {
        recordPropertyPriceChange(
            $id,
            $before['min_price'] !== null ? (float) $before['min_price'] : null,
            $minPrice,
            $before['max_price'] !== null ? (float) $before['max_price'] : null,
            $maxPrice
        );
        recordPropertyEditHistory($id, 'Property updated', [
            'name' => $before['name'] ?? null,
            'publication_status' => $before['publication_status'] ?? null,
            'min_price' => $before['min_price'] ?? null,
            'is_active' => $before['is_active'] ?? null,
        ], [
            'name' => $name,
            'publication_status' => $publication,
            'min_price' => $minPrice,
            'is_active' => $isActive,
        ]);
    } else {
        recordPropertyEditHistory($id, 'Property created', null, ['name' => $name, 'publication_status' => $publication]);
    }

    if ($publication === 'Published' && (!$wasPublished || $before === null)) {
        regenerateSitemap();
        createAdminAlert(
            'property_published',
            'New property published: ' . $name,
            'Sitemap updated after publish.',
            'info',
            'property',
            $id
        );
    }

    logAdminActivity($before !== null ? 'property_update' : 'property_create', 'property', $id, $name);
    adminFlash('success', 'Property saved.');
    adminRedirect('properties/edit.php?id=' . $id);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Property save failed: ' . $e->getMessage());
    adminFlash('error', 'Could not save property: ' . $e->getMessage());
    adminRedirect($id > 0 ? 'properties/edit.php?id=' . $id : 'properties/edit.php');
}
