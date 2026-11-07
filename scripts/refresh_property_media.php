<?php
declare(strict_types=1);

/**
 * Points seeded landmark projects at fresher project photography.
 * Keeps existing tour videos; safe to re-run.
 */

require dirname(__DIR__) . '/config/database.php';

$pdo = getDatabaseConnection();

$media = [
    'blue-ridge-hinjewadi' => [
        'images' => [
            'public/images/projects/pune-tower-1.jpg',
            'public/images/projects/pune-interior-1.jpg',
            'public/images/projects/pool-amenity.jpg',
        ],
        'videos' => ['public/videos/canopy-tour.mp4'],
    ],
    'amanora-gateway-towers' => [
        'images' => [
            'public/images/projects/pune-tower-2.jpg',
            'public/images/projects/luxury-lobby.jpg',
            'public/images/projects/pune-interior-1.jpg',
        ],
        'videos' => ['public/videos/eastline-tour.mp4'],
    ],
    'life-republic-hinjewadi' => [
        'images' => [
            'public/images/projects/skyline-dusk.jpg',
            'public/images/projects/pune-tower-1.jpg',
            'public/images/projects/pool-amenity.jpg',
        ],
        'videos' => ['public/videos/verdant-villas-tour.mp4'],
    ],
    'nyati-empress-kharadi' => [
        'images' => [
            'public/images/projects/pune-interior-1.jpg',
            'public/images/projects/pune-tower-2.jpg',
            'public/images/projects/luxury-lobby.jpg',
        ],
        'videos' => ['public/videos/meridian-tour.mp4'],
    ],
    'lodha-world-one' => [
        'images' => [
            'public/images/projects/mumbai-tower-1.jpg',
            'public/images/projects/mumbai-interior-1.jpg',
            'public/images/projects/skyline-dusk.jpg',
        ],
        'videos' => ['public/videos/arbor-tour.mp4'],
    ],
    'palais-royale-worli' => [
        'images' => [
            'public/images/projects/mumbai-tower-2.jpg',
            'public/images/projects/luxury-lobby.jpg',
            'public/images/projects/mumbai-interior-1.jpg',
        ],
        'videos' => ['public/videos/meridian-tour.mp4'],
    ],
    'oberoi-sky-city' => [
        'images' => [
            'public/images/projects/mumbai-tower-1.jpg',
            'public/images/projects/pool-amenity.jpg',
            'public/images/projects/mumbai-tower-2.jpg',
        ],
        'videos' => ['public/videos/foundry-offices-tour.mp4'],
    ],
    'hiranandani-gardens-powai' => [
        'images' => [
            'public/images/projects/mumbai-interior-1.jpg',
            'public/images/projects/mumbai-tower-2.jpg',
            'public/images/projects/pool-amenity.jpg',
        ],
        'videos' => ['public/videos/arbor-tour.mp4'],
    ],
    'dlf-the-camellias' => [
        'images' => [
            'public/images/projects/gurgaon-tower-1.jpg',
            'public/images/projects/gurgaon-interior-1.jpg',
            'public/images/projects/luxury-lobby.jpg',
        ],
        'videos' => ['public/videos/horizon-tour.mp4'],
    ],
    'm3m-golf-estate' => [
        'images' => [
            'public/images/projects/gurgaon-tower-2.jpg',
            'public/images/projects/pool-amenity.jpg',
            'public/images/projects/gurgaon-interior-1.jpg',
        ],
        'videos' => ['public/videos/verdant-villas-tour.mp4'],
    ],
    'emaar-digihomes' => [
        'images' => [
            'public/images/projects/gurgaon-interior-1.jpg',
            'public/images/projects/gurgaon-tower-1.jpg',
            'public/images/projects/skyline-dusk.jpg',
        ],
        'videos' => ['public/videos/foundry-offices-tour.mp4'],
    ],
    'godrej-meridien' => [
        'images' => [
            'public/images/projects/gurgaon-tower-2.jpg',
            'public/images/projects/gurgaon-tower-1.jpg',
            'public/images/projects/gurgaon-interior-1.jpg',
        ],
        'videos' => ['public/videos/horizon-tour.mp4'],
    ],
];

$find = $pdo->prepare('SELECT id FROM properties WHERE slug = ? AND deleted_at IS NULL LIMIT 1');
$insImg = $pdo->prepare(
    'INSERT INTO property_images (property_id, image_path, sort_order, media_type) VALUES (?, ?, ?, \'image\')'
);
$insVid = $pdo->prepare(
    'INSERT INTO property_videos (property_id, video_path, sort_order) VALUES (?, ?, ?)'
);

foreach ($media as $slug => $bundle) {
    $find->execute([$slug]);
    $id = $find->fetchColumn();
    if ($id === false) {
        echo "skip {$slug}\n";
        continue;
    }
    $propertyId = (int) $id;
    $pdo->prepare('DELETE FROM property_images WHERE property_id = ?')->execute([$propertyId]);
    $pdo->prepare('DELETE FROM property_videos WHERE property_id = ?')->execute([$propertyId]);
    foreach ($bundle['images'] as $i => $path) {
        $insImg->execute([$propertyId, $path, $i]);
    }
    foreach ($bundle['videos'] as $i => $path) {
        $insVid->execute([$propertyId, $path, $i]);
    }
    echo "media {$slug}\n";
}

echo "DONE\n";
