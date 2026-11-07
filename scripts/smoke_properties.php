<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/properties.php';

$list = properties();
echo 'count=' . count($list) . PHP_EOL;
foreach ($list as $property) {
    echo $property['id'] . ' | ' . $property['name'] . ' | ' . $property['city'] . ' | ' . $property['price_label'] . PHP_EOL;
}

$one = propertyById('canopy');
echo 'canopy_db_id=' . ($one['db_id'] ?? 'missing') . PHP_EOL;
echo 'canopy_amenities=' . count($one['amenities_list'] ?? []) . PHP_EOL;

$filtered = filterProperties(['city' => 'Mumbai']);
echo 'mumbai_count=' . count($filtered) . PHP_EOL;
