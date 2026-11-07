<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__) . '/config/database.php';

function cityState(string $city): string
{
    return match ($city) {
        'Gurgaon' => 'Haryana',
        default => 'Maharashtra',
    };
}

function formatPriceLabel(?float $minPrice, ?float $maxPrice = null): string
{
    if ($minPrice === null || $minPrice <= 0) {
        return 'Price on request';
    }

    $formatOne = static function (float $amount): string {
        if ($amount >= 10000000) {
            $crore = $amount / 10000000;
            $label = rtrim(rtrim(number_format($crore, 2, '.', ''), '0'), '.');

            return '₹' . $label . ' Cr*';
        }

        $lakh = $amount / 100000;
        $label = rtrim(rtrim(number_format($lakh, 2, '.', ''), '0'), '.');

        return '₹' . $label . ' L*';
    };

    if ($maxPrice !== null && $maxPrice > $minPrice) {
        return $formatOne($minPrice) . ' – ' . $formatOne($maxPrice);
    }

    return $formatOne($minPrice);
}

function formatAreaLabel(?float $sqft): string
{
    if ($sqft === null || $sqft <= 0) {
        return 'Size on request';
    }

    return number_format($sqft, 0) . ' sq. ft.';
}

function priceInLakhs(?float $amount): int
{
    if ($amount === null || $amount <= 0) {
        return 0;
    }

    return (int) round($amount / 100000);
}

function mapPropertyRow(array $row, bool $withRelations = false): array
{
    $minPrice = $row['min_price'] !== null ? (float) $row['min_price'] : null;
    $maxPrice = $row['max_price'] !== null ? (float) $row['max_price'] : null;
    $sizeSqft = isset($row['size_sqft']) && $row['size_sqft'] !== null ? (float) $row['size_sqft'] : null;
    $slug = (string) ($row['slug'] ?: $row['id']);
    $city = (string) $row['city_name'];
    $image = (string) ($row['primary_image'] ?? '');
    $video = (string) ($row['primary_video'] ?? '');

    $property = [
        'id' => $slug,
        'db_id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'slug' => $slug,
        'city' => $city,
        'state' => cityState($city),
        'area' => (string) $row['area_name'],
        'type' => (string) ($row['property_type'] ?? ''),
        'status' => (string) ($row['property_status'] ?? ''),
        'bhk' => (string) ($row['bhk'] ?? ''),
        'price' => priceInLakhs($minPrice),
        'price_label' => formatPriceLabel($minPrice, $maxPrice),
        'size' => formatAreaLabel($sizeSqft),
        'size_sqft' => $sizeSqft,
        'image' => $image !== '' ? $image : 'public/images/hero.jpg',
        'video' => $video,
        'featured' => (bool) $row['is_featured'],
        'summary' => (string) ($row['overview'] ?? ''),
        'overview' => (string) ($row['overview'] ?? ''),
        'address' => (string) ($row['address'] ?? ''),
        'latitude' => $row['latitude'] !== null ? (float) $row['latitude'] : null,
        'longitude' => $row['longitude'] !== null ? (float) $row['longitude'] : null,
        'map_url' => (string) ($row['map_url'] ?? ''),
        'construction_status' => (string) ($row['construction_status'] ?? $row['property_status'] ?? ''),
        'construction_progress' => $row['construction_progress'] !== null ? (int) $row['construction_progress'] : null,
        'possession_date' => $row['possession_date'] ?? null,
        'builtup_min_sqft' => isset($row['builtup_min_sqft']) && $row['builtup_min_sqft'] !== null ? (float) $row['builtup_min_sqft'] : null,
        'builtup_max_sqft' => isset($row['builtup_max_sqft']) && $row['builtup_max_sqft'] !== null ? (float) $row['builtup_max_sqft'] : null,
        'builtup_label' => formatBuiltupLabel(
            isset($row['builtup_min_sqft']) && $row['builtup_min_sqft'] !== null ? (float) $row['builtup_min_sqft'] : null,
            isset($row['builtup_max_sqft']) && $row['builtup_max_sqft'] !== null ? (float) $row['builtup_max_sqft'] : null
        ),
        'about_builder' => (string) ($row['about_builder'] ?? ''),
        'launch_date' => $row['launch_date'] ?? null,
        'seo_title' => (string) ($row['seo_title'] ?? ''),
        'seo_description' => (string) ($row['seo_description'] ?? ''),
        'publication_status' => (string) ($row['publication_status'] ?? ''),
        'amenities_list' => [],
        'gallery' => [],
        'videos' => [],
        'unit_types' => [],
        'brochure_path' => null,
    ];

    if ($withRelations) {
        $property = enrichPropertyRelations($property);
    }

    return $property;
}

function ensurePropertyColumns(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    $pdo = getDatabaseConnection();
    $existing = $pdo->query('SHOW COLUMNS FROM properties')->fetchAll(PDO::FETCH_COLUMN);
    $add = [];
    if (!in_array('builtup_min_sqft', $existing, true)) {
        $add[] = 'ADD COLUMN builtup_min_sqft DECIMAL(10,2) NULL';
    }
    if (!in_array('builtup_max_sqft', $existing, true)) {
        $add[] = 'ADD COLUMN builtup_max_sqft DECIMAL(10,2) NULL';
    }
    if (!in_array('about_builder', $existing, true)) {
        $add[] = 'ADD COLUMN about_builder TEXT NULL';
    }
    if (!in_array('launch_date', $existing, true)) {
        $add[] = 'ADD COLUMN launch_date DATE NULL';
    }
    if ($add !== []) {
        $pdo->exec('ALTER TABLE properties ' . implode(', ', $add));
    }
}

function formatBuiltupLabel(?float $min, ?float $max): string
{
    $min = $min !== null && $min > 0 ? $min : null;
    $max = $max !== null && $max > 0 ? $max : null;
    if ($min === null && $max === null) {
        return '';
    }
    if ($min !== null && $max !== null && $max > $min) {
        return number_format($min, 0) . '–' . number_format($max, 0) . ' sq. ft.';
    }

    return number_format($min ?? $max, 0) . ' sq. ft.';
}

function propertySelectSql(string $extraWhere = '', string $orderBy = 'p.sort_order ASC, p.id ASC'): string
{
    ensurePropertyColumns();
    $where = "p.is_active = 1 AND p.publication_status = 'Published' AND p.deleted_at IS NULL";
    if ($extraWhere !== '') {
        $where .= ' AND (' . $extraWhere . ')';
    }

    return "
        SELECT
            p.*,
            c.name AS city_name,
            a.name AS area_name,
            (
                SELECT pi.image_path
                FROM property_images pi
                WHERE pi.property_id = p.id
                ORDER BY pi.sort_order ASC, pi.id ASC
                LIMIT 1
            ) AS primary_image,
            (
                SELECT pv.video_path
                FROM property_videos pv
                WHERE pv.property_id = p.id
                ORDER BY pv.sort_order ASC, pv.id ASC
                LIMIT 1
            ) AS primary_video,
            (
                SELECT ut.carpet_area_sqft
                FROM property_unit_types ut
                WHERE ut.property_id = p.id
                ORDER BY ut.sort_order ASC, ut.id ASC
                LIMIT 1
            ) AS size_sqft
        FROM properties p
        INNER JOIN cities c ON c.id = p.city_id
        INNER JOIN areas a ON a.id = p.area_id
        WHERE {$where}
        ORDER BY {$orderBy}
    ";
}

function enrichPropertyRelations(array $property): array
{
    $pdo = getDatabaseConnection();
    $dbId = (int) $property['db_id'];

    $amenities = $pdo->prepare(
        'SELECT amenity_name FROM property_amenities WHERE property_id = ? ORDER BY id ASC'
    );
    $amenities->execute([$dbId]);
    $property['amenities_list'] = $amenities->fetchAll(PDO::FETCH_COLUMN);

    $images = $pdo->prepare(
        'SELECT image_path FROM property_images WHERE property_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $images->execute([$dbId]);
    $property['gallery'] = $images->fetchAll(PDO::FETCH_COLUMN);

    $videos = $pdo->prepare(
        'SELECT video_path FROM property_videos WHERE property_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $videos->execute([$dbId]);
    $property['videos'] = $videos->fetchAll(PDO::FETCH_COLUMN);
    if ($property['video'] === '' && $property['videos'] !== []) {
        $property['video'] = (string) $property['videos'][0];
    }

    $units = $pdo->prepare(
        'SELECT configuration, carpet_area_sqft, price, floor_plan_path, description
         FROM property_unit_types
         WHERE property_id = ?
         ORDER BY sort_order ASC, id ASC'
    );
    $units->execute([$dbId]);
    $property['unit_types'] = $units->fetchAll();

    $brochure = $pdo->prepare(
        'SELECT brochure_path FROM property_brochures WHERE property_id = ? ORDER BY id DESC LIMIT 1'
    );
    $brochure->execute([$dbId]);
    $path = $brochure->fetchColumn();
    $property['brochure_path'] = $path !== false ? (string) $path : null;

    return $property;
}

function properties(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $pdo = getDatabaseConnection();
    $rows = $pdo->query(propertySelectSql())->fetchAll();
    $cache = array_map(static fn (array $row): array => mapPropertyRow($row), $rows);

    return $cache;
}

function propertyById(string $id): ?array
{
    $id = trim($id);
    if ($id === '') {
        return null;
    }

    $pdo = getDatabaseConnection();
    $sql = propertySelectSql('p.slug = :lookup OR p.id = :lookup_id');
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'lookup' => $id,
        'lookup_id' => ctype_digit($id) ? (int) $id : 0,
    ]);
    $row = $stmt->fetch();
    if ($row === false) {
        return null;
    }

    return mapPropertyRow($row, true);
}

function featuredProperty(): ?array
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(propertySelectSql('p.is_featured = 1', 'p.sort_order ASC, p.id ASC') . ' LIMIT 1');
    $row = $stmt->fetch();
    if ($row === false) {
        return null;
    }

    return mapPropertyRow($row, true);
}

function featuredProperties(int $limit = 6): array
{
    $limit = max(1, min(24, $limit));
    $pdo = getDatabaseConnection();
    $rows = $pdo->query(
        propertySelectSql('p.is_featured = 1', 'p.sort_order ASC, p.id ASC') . ' LIMIT ' . $limit
    )->fetchAll();

    $list = array_map(static fn (array $row): array => mapPropertyRow($row), $rows);
    if (count($list) >= $limit) {
        return array_slice($list, 0, $limit);
    }

    $seen = [];
    foreach ($list as $item) {
        $seen[(string) $item['id']] = true;
    }

    foreach (properties() as $property) {
        if (isset($seen[(string) $property['id']])) {
            continue;
        }
        $list[] = $property;
        $seen[(string) $property['id']] = true;
        if (count($list) >= $limit) {
            break;
        }
    }

    return array_slice($list, 0, $limit);
}

function propertyMapEmbedUrl(array $property): string
{
    $mapUrl = trim((string) ($property['map_url'] ?? ''));
    if ($mapUrl !== '' && (str_contains($mapUrl, '/maps/embed') || str_contains($mapUrl, 'output=embed'))) {
        return $mapUrl;
    }

    $lat = $property['latitude'] ?? null;
    $lng = $property['longitude'] ?? null;
    if ($lat !== null && $lng !== null) {
        $query = rawurlencode(((float) $lat) . ',' . ((float) $lng));
        return 'https://www.google.com/maps?q=' . $query . '&z=15&output=embed';
    }

    $label = trim((string) ($property['address'] ?? ''));
    if ($label === '') {
        $label = trim(
            (string) ($property['area'] ?? '') . ', ' .
            (string) ($property['city'] ?? '') . ', ' .
            (string) ($property['state'] ?? ''),
            " \t\n\r\0\x0B,"
        );
    }
    if ($label === '' && $mapUrl !== '') {
        $label = $mapUrl;
    }
    if ($label === '') {
        return '';
    }

    return 'https://www.google.com/maps?q=' . rawurlencode($label) . '&output=embed';
}

function preferredCityOrder(): array
{
    return ['Pune', 'Mumbai', 'Gurgaon'];
}

function formatPlaceName(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '';
    }

    return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

function sortByPreferredCities(array $names): array
{
    $rank = [];
    foreach (preferredCityOrder() as $index => $city) {
        $rank[mb_strtolower($city, 'UTF-8')] = $index;
    }

    usort($names, static function (string $a, string $b) use ($rank): int {
        $left = $rank[mb_strtolower($a, 'UTF-8')] ?? 100;
        $right = $rank[mb_strtolower($b, 'UTF-8')] ?? 100;
        if ($left === $right) {
            return strcasecmp($a, $b);
        }

        return $left <=> $right;
    });

    $seen = [];
    $ordered = [];
    foreach ($names as $name) {
        $key = mb_strtolower(trim($name), 'UTF-8');
        if ($key === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $ordered[] = $name;
    }

    return $ordered;
}

function propertyChoices(string $key): array
{
    if ($key === 'city') {
        $pdo = getDatabaseConnection();
        $names = $pdo->query('SELECT name FROM cities')->fetchAll(PDO::FETCH_COLUMN);

        return sortByPreferredCities(array_map('strval', $names));
    }

    if ($key === 'area') {
        $areas = areasByCity();
        $values = [];

        foreach ($areas as $cityAreas) {
            foreach ($cityAreas as $area) {
                $values[$area] = $area;
            }
        }

        return array_values($values);
    }

    $column = match ($key) {
        'type' => 'property_type',
        'status' => 'property_status',
        'bhk' => 'bhk',
        default => null,
    };

    if ($column === null) {
        return [];
    }

    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("
        SELECT DISTINCT {$column}
        FROM properties
        WHERE is_active = 1
          AND publication_status = 'Published'
          AND deleted_at IS NULL
          AND {$column} IS NOT NULL
          AND {$column} <> ''
        ORDER BY {$column}
    ");

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function areasByCity(): array
{
    $pdo = getDatabaseConnection();

    $stmt = $pdo->query("
        SELECT c.name AS city, a.name AS area
        FROM areas a
        INNER JOIN cities c ON c.id = a.city_id
        ORDER BY c.name, a.name
    ");

    $grouped = [];

    foreach ($stmt->fetchAll() as $row) {
        $city = (string) $row['city'];
        $grouped[$city][] = (string) $row['area'];
    }

    $ordered = [];
    foreach (sortByPreferredCities(array_keys($grouped)) as $city) {
        $ordered[$city] = $grouped[$city];
    }

    return $ordered;
}

function budgetRanges(): array
{
    return [
        '' => 'Any budget',
        '0-75' => 'Under ₹75 lakh',
        '75-100' => '₹75 lakh – ₹1 crore',
        '100-200' => '₹1 – ₹2 crore',
        '200-500' => '₹2 – ₹5 crore',
        '500-10000' => '₹5 crore+',
    ];
}

function filterProperties(array $query): array
{
    $pdo = getDatabaseConnection();
    $where = [];
    $params = [];

    $city = trim((string) ($query['city'] ?? ''));
    if ($city !== '') {
        $where[] = 'c.name = :city';
        $params['city'] = $city;
    }

    $area = trim((string) ($query['area'] ?? ''));
    if ($area !== '') {
        $where[] = 'a.name = :area';
        $params['area'] = $area;
    }

    $type = trim((string) ($query['type'] ?? ''));
    if ($type !== '') {
        $where[] = 'p.property_type = :ptype';
        $params['ptype'] = $type;
    }

    $status = trim((string) ($query['status'] ?? ''));
    if ($status !== '') {
        $where[] = 'p.property_status = :pstatus';
        $params['pstatus'] = $status;
    }

    $bhk = trim((string) ($query['bhk'] ?? ''));
    if ($bhk !== '') {
        $where[] = 'p.bhk = :bhk';
        $params['bhk'] = $bhk;
    }

    $budget = (string) ($query['budget'] ?? '');
    if ($budget !== '' && preg_match('/^(\d+)-(\d+)$/', $budget, $match) === 1) {
        $minLakhs = (int) $match[1];
        $maxLakhs = (int) $match[2];
        $where[] = 'p.min_price IS NOT NULL AND p.min_price > 0';
        $where[] = 'p.min_price >= :min_price';
        $where[] = 'p.min_price <= :max_price';
        $params['min_price'] = $minLakhs * 100000;
        $params['max_price'] = $maxLakhs * 100000;
    }

    $extra = $where === [] ? '' : implode(' AND ', $where);
    $sql = propertySelectSql($extra);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_map(static fn (array $row): array => mapPropertyRow($row), $stmt->fetchAll());
}

function propertyUrl(string $id): string
{
    $id = trim($id);
    if ($id === '') {
        return webPath('pages/properties');
    }

    return webPath('property/' . rawurlencode($id));
}

function propertyBuilderLabel(array $property): string
{
    $about = trim((string) ($property['about_builder'] ?? ''));
    if ($about === '') {
        return '';
    }
    $parts = preg_split('/\s+[—–-]\s+/u', $about, 2) ?: [];
    return trim((string) ($parts[0] ?? ''));
}

function renderPropertyCard(array $property, int $revealIndex = 0): void
{
    $image = webPath($property['image']);
    $video = $property['video'] !== '' ? webPath($property['video']) : '';
    $builder = propertyBuilderLabel($property);
    $areaLabel = trim((string) ($property['builtup_label'] ?? '')) !== ''
        ? (string) $property['builtup_label']
        : (string) ($property['size'] ?? '');
    ?>
    <article class="property property-card-motion" style="--card-delay: <?= max(0, min(8, $revealIndex)) ?>" data-city="<?= h($property['city']) ?>" data-type="<?= h($property['type']) ?>" data-bhk="<?= h($property['bhk']) ?>" data-price="<?= h((string) $property['price']) ?>">
        <a class="property-link" href="<?= h(propertyUrl($property['id'])) ?>">
            <div class="property-image">
                <span class="pv" aria-hidden="true"></span>
                <span class="pvt"><?= $video !== '' ? 'Watch tour' : 'Preview' ?></span>
                <img loading="lazy" src="<?= h($image) ?>" alt="<?= h($property['name']) ?>">
                <?php if ($video !== ''): ?>
                    <video class="property-video" muted playsinline preload="metadata" src="<?= h($video) ?>"></video>
                <?php endif; ?>
                <span class="tag"><?= h($property['status']) ?> · <?= h($property['city']) ?></span>
            </div>
            <div class="property-body">
                <div class="location"><?= h($property['area']) ?> · <?= h($property['city']) ?></div>
                <h3><?= h($property['name']) ?></h3>
                <?php if ($builder !== ''): ?>
                    <div class="developer">By <?= h($builder) ?></div>
                <?php endif; ?>
                <div class="specs">
                    <span><?= h($property['bhk']) ?></span>
                    <?php if ($areaLabel !== ''): ?>
                        <span><?= h($areaLabel) ?></span>
                    <?php endif; ?>
                    <span><?= h($property['type']) ?></span>
                </div>
                <div class="price-row">
                    <div class="price"><?= h($property['price_label']) ?></div>
                    <span class="arrow">View ↗</span>
                </div>
            </div>
        </a>
        <button class="heart" type="button" aria-label="Save <?= h($property['name']) ?>" aria-pressed="false">♡</button>
        <button class="btn outline visit-open" type="button" data-intent="visit" data-property="<?= h($property['id']) ?>">Request Site Visit</button>
    </article>
    <?php
}

function renderPropertyFilter(array $selected = []): void
{
    $areas = areasByCity();
    $city = (string) ($selected['city'] ?? '');
    $areaChoices = $city !== '' && isset($areas[$city]) ? $areas[$city] : propertyChoices('area');
    ?>
    <form class="search-box property-filter" method="get" action="<?= h(webPath('pages/properties')) ?>">
        <div class="field filter-field">
            <label for="filterCity">
                <span class="filter-icon">⌖</span>
                <span class="filter-label">City</span>
            </label>
            <select id="filterCity" name="city">
                <option value="">All cities</option>
                <?php foreach (propertyChoices('city') as $choice): ?>
                    <option value="<?= h($choice) ?>"<?= $city === $choice ? ' selected' : '' ?>><?= h(formatPlaceName($choice)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field area-picker">
        <label for="areaSearch">
            <span class="filter-icon">⌕</span>
            <span class="filter-label">Area</span>
        </label>

    <input
        type="text"
        id="areaSearch"
        placeholder="Search area..."
        autocomplete="off"
        value="<?= h((string) ($selected['area'] ?? '')) ?>"
        <?= $city === '' ? 'disabled' : '' ?>
    >

    <input
        type="hidden"
        id="filterArea"
        name="area"
        value="<?= h((string) ($selected['area'] ?? '')) ?>"
    >

    <div id="areaDropdown" class="area-dropdown" hidden></div>
</div>
        <div class="field">
            <label for="Property type">
                 <span class="filter-icon">⌂</span>
        <span class="filter-label">Property type</span>
            </label>
            <select id="filterType" name="type">
                <option value="">All types</option>
                <?php foreach (propertyChoices('type') as $choice): ?>
                    <option<?= (($selected['type'] ?? '') === $choice) ? ' selected' : '' ?>><?= h($choice) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="Status">
                 <span class="filter-icon">◷</span>
        <span class="filter-label">Status</span>
            </label>
            <select id="filterStatus" name="status">
                <option value="">Any status</option>
                <?php foreach (propertyChoices('status') as $choice): ?>
                    <option<?= (($selected['status'] ?? '') === $choice) ? ' selected' : '' ?>><?= h($choice) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="BHK">
                 <span class="filter-icon">▦</span>
        <span class="filter-label">BHK</span>
            </label>
            <select id="filterBhk" name="bhk">
                <option value="">Any BHK</option>
                <?php foreach (propertyChoices('bhk') as $choice): ?>
                    <option<?= (($selected['bhk'] ?? '') === $choice) ? ' selected' : '' ?>><?= h($choice) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="Budget">
                 <span class="filter-icon">₹</span>
        <span class="filter-label">Budget</span>
            </label>
            <select id="filterBudget" name="budget">
                <?php foreach (budgetRanges() as $value => $label): ?>
                    <option value="<?= h($value) ?>"<?= (($selected['budget'] ?? '') === $value) ? ' selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Find a property ↗</button>
    </form>
    <div class="filter-actions">
        <button type="button" class="clear-filters" id="clearFilters">
            Clear all filters ↺
        </button>
    </div>
    <script type="application/json" id="areaMap"><?= json_encode($areas, JSON_UNESCAPED_UNICODE) ?></script>
    <?php
}

function countryCodes(): array
{
    $data = <<<'CODES'
India|+91
Afghanistan|+93
Albania|+355
Algeria|+213
American Samoa|+1-684
Andorra|+376
Angola|+244
Anguilla|+1-264
Antigua and Barbuda|+1-268
Argentina|+54
Armenia|+374
Aruba|+297
Australia|+61
Austria|+43
Azerbaijan|+994
Bahamas|+1-242
Bahrain|+973
Bangladesh|+880
Barbados|+1-246
Belarus|+375
Belgium|+32
Belize|+501
Benin|+229
Bermuda|+1-441
Bhutan|+975
Bolivia|+591
Bosnia and Herzegovina|+387
Botswana|+267
Brazil|+55
British Virgin Islands|+1-284
Brunei|+673
Bulgaria|+359
Burkina Faso|+226
Burundi|+257
Cambodia|+855
Cameroon|+237
Canada|+1
Cape Verde|+238
Cayman Islands|+1-345
Central African Republic|+236
Chad|+235
Chile|+56
China|+86
Colombia|+57
Comoros|+269
Congo - Brazzaville|+242
Congo - Kinshasa|+243
Cook Islands|+682
Costa Rica|+506
Croatia|+385
Cuba|+53
Curacao|+599
Cyprus|+357
Czechia|+420
Denmark|+45
Djibouti|+253
Dominica|+1-767
Dominican Republic|+1-809
Ecuador|+593
Egypt|+20
El Salvador|+503
Equatorial Guinea|+240
Eritrea|+291
Estonia|+372
Eswatini|+268
Ethiopia|+251
Faroe Islands|+298
Fiji|+679
Finland|+358
France|+33
French Guiana|+594
French Polynesia|+689
Gabon|+241
Gambia|+220
Georgia|+995
Germany|+49
Ghana|+233
Gibraltar|+350
Greece|+30
Greenland|+299
Grenada|+1-473
Guadeloupe|+590
Guam|+1-671
Guatemala|+502
Guernsey|+44-1481
Guinea|+224
Guinea-Bissau|+245
Guyana|+592
Haiti|+509
Honduras|+504
Hong Kong|+852
Hungary|+36
Iceland|+354
Indonesia|+62
Iran|+98
Iraq|+964
Ireland|+353
Isle of Man|+44-1624
Israel|+972
Italy|+39
Ivory Coast|+225
Jamaica|+1-876
Japan|+81
Jersey|+44-1534
Jordan|+962
Kazakhstan|+7
Kenya|+254
Kiribati|+686
Kosovo|+383
Kuwait|+965
Kyrgyzstan|+996
Laos|+856
Latvia|+371
Lebanon|+961
Lesotho|+266
Liberia|+231
Libya|+218
Liechtenstein|+423
Lithuania|+370
Luxembourg|+352
Macau|+853
Madagascar|+261
Malawi|+265
Malaysia|+60
Maldives|+960
Mali|+223
Malta|+356
Marshall Islands|+692
Martinique|+596
Mauritania|+222
Mauritius|+230
Mayotte|+262
Mexico|+52
Micronesia|+691
Moldova|+373
Monaco|+377
Mongolia|+976
Montenegro|+382
Montserrat|+1-664
Morocco|+212
Mozambique|+258
Myanmar|+95
Namibia|+264
Nauru|+674
Nepal|+977
Netherlands|+31
New Caledonia|+687
New Zealand|+64
Nicaragua|+505
Niger|+227
Nigeria|+234
Niue|+683
North Korea|+850
North Macedonia|+389
Northern Mariana Islands|+1-670
Norway|+47
Oman|+968
Pakistan|+92
Palau|+680
Palestine|+970
Panama|+507
Papua New Guinea|+675
Paraguay|+595
Peru|+51
Philippines|+63
Poland|+48
Portugal|+351
Puerto Rico|+1-787
Qatar|+974
Reunion|+262
Romania|+40
Russia|+7
Rwanda|+250
Saint Kitts and Nevis|+1-869
Saint Lucia|+1-758
Saint Martin|+590
Saint Pierre and Miquelon|+508
Saint Vincent and the Grenadines|+1-784
Samoa|+685
San Marino|+378
Sao Tome and Principe|+239
Saudi Arabia|+966
Senegal|+221
Serbia|+381
Seychelles|+248
Sierra Leone|+232
Singapore|+65
Sint Maarten|+1-721
Slovakia|+421
Slovenia|+386
Solomon Islands|+677
Somalia|+252
South Africa|+27
South Korea|+82
South Sudan|+211
Spain|+34
Sri Lanka|+94
Sudan|+249
Suriname|+597
Sweden|+46
Switzerland|+41
Syria|+963
Taiwan|+886
Tajikistan|+992
Tanzania|+255
Thailand|+66
Timor-Leste|+670
Togo|+228
Tonga|+676
Trinidad and Tobago|+1-868
Tunisia|+216
Turkey|+90
Turkmenistan|+993
Turks and Caicos Islands|+1-649
Tuvalu|+688
Uganda|+256
Ukraine|+380
United Arab Emirates|+971
United Kingdom|+44
United States|+1
United States Virgin Islands|+1-340
Uruguay|+598
Uzbekistan|+998
Vanuatu|+678
Vatican City|+379
Venezuela|+58
Vietnam|+84
Wallis and Futuna|+681
Western Sahara|+212
Yemen|+967
Zambia|+260
Zimbabwe|+263
CODES;

    return array_map(
        static fn (string $entry): array => explode('|', $entry, 2),
        explode("\n", trim($data))
    );
}
