<?php
declare(strict_types=1);

/**
 * Publish readiness for property CMS (Phase 5).
 * Required: name, city, area, starting price > 0, at least one image.
 *
 * @return array{ready:bool,checks:array<int,array{key:string,label:string,ok:bool,hint:string}>}
 */
function propertyPublishChecklist(
    string $name,
    int $cityId,
    int $areaId,
    ?float $minPrice,
    int $imageCount
): array {
    $checks = [
        [
            'key' => 'name',
            'label' => 'Property name',
            'ok' => $name !== '',
            'hint' => 'Add a clear listing name.',
        ],
        [
            'key' => 'city',
            'label' => 'City',
            'ok' => $cityId > 0,
            'hint' => 'Choose a city.',
        ],
        [
            'key' => 'area',
            'label' => 'Area / location',
            'ok' => $areaId > 0,
            'hint' => 'Choose an area in that city.',
        ],
        [
            'key' => 'price',
            'label' => 'Starting price',
            'ok' => $minPrice !== null && $minPrice > 0,
            'hint' => 'Enter a starting price greater than zero.',
        ],
        [
            'key' => 'image',
            'label' => 'At least one image',
            'ok' => $imageCount >= 1,
            'hint' => 'Upload a gallery image before publishing.',
        ],
    ];

    $ready = true;
    foreach ($checks as $check) {
        if (!$check['ok']) {
            $ready = false;
            break;
        }
    }

    return [
        'ready' => $ready,
        'checks' => $checks,
    ];
}

function propertyPublishMissingLabels(array $checklist): array
{
    $missing = [];
    foreach ($checklist['checks'] as $check) {
        if (!$check['ok']) {
            $missing[] = (string) $check['label'];
        }
    }
    return $missing;
}
