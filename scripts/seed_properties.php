<?php
declare(strict_types=1);

/**
 * Seeds landmark towers / projects across Pune, Mumbai and Gurgaon.
 * Safe to re-run: upserts by slug. Soft-deletes older demo listings not in this set.
 */

require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/properties.php';

ensurePropertyColumns();

$pdo = getDatabaseConnection();
$pdo->beginTransaction();

try {
    $cityIds = [];
    foreach ($pdo->query('SELECT id, name FROM cities') as $row) {
        $cityIds[$row['name']] = (int) $row['id'];
    }

    $areaStmt = $pdo->prepare('SELECT id FROM areas WHERE city_id = :city_id AND name = :name LIMIT 1');
    $resolveArea = static function (int $cityId, string $name) use ($pdo, $areaStmt): int {
        $areaStmt->execute(['city_id' => $cityId, 'name' => $name]);
        $id = $areaStmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }

        $insert = $pdo->prepare('INSERT INTO areas (city_id, name) VALUES (:city_id, :name)');
        $insert->execute(['city_id' => $cityId, 'name' => $name]);
        return (int) $pdo->lastInsertId();
    };

    $img = static fn (string ...$paths): array => array_values($paths);
    $samples = [
        // Pune
        [
            'slug' => 'blue-ridge-hinjewadi',
            'name' => 'Blue Ridge',
            'city' => 'Pune',
            'area' => 'Hinjewadi',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '2, 3 BHK',
            'min_price' => 12500000,
            'max_price' => 24500000,
            'size_sqft' => 1280,
            'builtup_min' => 1050,
            'builtup_max' => 1850,
            'images' => $img('public/images/projects/pune-tower-1.jpg', 'public/images/projects/pune-interior-1.jpg', 'public/images/projects/pool-amenity.jpg'),
            'videos' => ['public/videos/canopy-tour.mp4'],
            'featured' => 1,
            'sort_order' => 1,
            'builder' => 'Paranjape Schemes — known for township-scale residential communities across Pune.',
            'overview' => 'A landmark Hinjewadi address with landscaped courtyards, club amenities and strong IT-corridor connectivity.',
            'amenities' => ['Clubhouse', 'Swimming pool', 'Landscaped gardens', 'Fitness centre', 'Covered parking'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2023-06-01',
            'launch_date' => '2014-03-01',
            'rera' => 'P52100001234',
            'latitude' => 18.5912,
            'longitude' => 73.7389,
        ],
        [
            'slug' => 'amanora-gateway-towers',
            'name' => 'Amanora Gateway Towers',
            'city' => 'Pune',
            'area' => 'Hadapsar',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '2, 3, 4 BHK',
            'min_price' => 14500000,
            'max_price' => 42000000,
            'size_sqft' => 1520,
            'builtup_min' => 1180,
            'builtup_max' => 2650,
            'images' => $img('public/images/projects/pune-tower-2.jpg', 'public/images/projects/luxury-lobby.jpg', 'public/images/projects/pune-interior-1.jpg'),
            'videos' => ['public/videos/eastline-tour.mp4'],
            'featured' => 1,
            'sort_order' => 2,
            'builder' => 'City Corporation Limited — developer of Amanora Park Town.',
            'overview' => 'High-rise residences inside Amanora Park Town with retail, schools and open spaces in one planned precinct.',
            'amenities' => ['Clubhouse', 'Mall access', 'Jogging track', 'Children play area', 'Security'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2022-12-01',
            'launch_date' => '2016-08-01',
            'rera' => 'P52100002345',
            'latitude' => 18.5195,
            'longitude' => 73.9345,
        ],
        [
            'slug' => 'life-republic-hinjewadi',
            'name' => 'Life Republic',
            'city' => 'Pune',
            'area' => 'Hinjewadi',
            'type' => 'Apartment',
            'status' => 'Under Construction',
            'bhk' => '1, 2, 3 BHK',
            'min_price' => 7800000,
            'max_price' => 16800000,
            'size_sqft' => 980,
            'builtup_min' => 620,
            'builtup_max' => 1450,
            'images' => $img('public/images/projects/skyline-dusk.jpg', 'public/images/projects/pune-tower-1.jpg', 'public/images/projects/pool-amenity.jpg'),
            'videos' => ['public/videos/verdant-villas-tour.mp4'],
            'featured' => 0,
            'sort_order' => 3,
            'builder' => 'Kolte-Patil Developers — listed residential developer with projects across Pune and beyond.',
            'overview' => 'A large integrated township near Hinjewadi with multiple residential clusters and township amenities.',
            'amenities' => ['Clubhouse', 'Sports courts', 'Landscaped gardens', 'School campus', 'Visitor parking'],
            'construction_status' => 'Under Construction',
            'construction_progress' => 68,
            'possession_date' => '2027-03-01',
            'launch_date' => '2018-01-01',
            'rera' => 'P52100003456',
            'latitude' => 18.6078,
            'longitude' => 73.7104,
        ],
        [
            'slug' => 'nyati-empress-kharadi',
            'name' => 'Nyati Empress',
            'city' => 'Pune',
            'area' => 'Kharadi',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '2, 3 BHK',
            'min_price' => 11200000,
            'max_price' => 19800000,
            'size_sqft' => 1180,
            'builtup_min' => 980,
            'builtup_max' => 1620,
            'images' => $img('public/images/projects/pune-interior-1.jpg', 'public/images/projects/pune-tower-2.jpg', 'public/images/projects/luxury-lobby.jpg'),
            'videos' => ['public/videos/meridian-tour.mp4'],
            'featured' => 0,
            'sort_order' => 4,
            'builder' => 'Nyati Group — Pune-focused residential developer.',
            'overview' => 'Premium residences in Kharadi with easy access to EON IT Park and the city’s eastern business corridor.',
            'amenities' => ['Clubhouse', 'Gym', 'Swimming pool', 'Power backup', 'Covered parking'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2024-09-01',
            'launch_date' => '2019-05-01',
            'rera' => 'P52100004567',
            'latitude' => 18.5516,
            'longitude' => 73.9422,
        ],

        // Mumbai
        [
            'slug' => 'lodha-world-one',
            'name' => 'Lodha World One',
            'city' => 'Mumbai',
            'area' => 'Upper Worli',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '3, 4, 5 BHK',
            'min_price' => 95000000,
            'max_price' => 250000000,
            'size_sqft' => 2450,
            'builtup_min' => 2100,
            'builtup_max' => 5200,
            'images' => $img('public/images/projects/mumbai-tower-1.jpg', 'public/images/projects/mumbai-interior-1.jpg', 'public/images/projects/skyline-dusk.jpg'),
            'videos' => ['public/videos/arbor-tour.mp4'],
            'featured' => 1,
            'sort_order' => 5,
            'builder' => 'Lodha Group — one of India’s largest real-estate developers.',
            'overview' => 'A signature super-tall residence on the World Towers campus with sweeping city and sea-facing views.',
            'amenities' => ['Private club', 'Infinity pool', 'Concierge', 'Spa', 'Valet parking'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2021-12-01',
            'launch_date' => '2010-06-01',
            'rera' => 'P51900005678',
            'latitude' => 19.0048,
            'longitude' => 72.8195,
        ],
        [
            'slug' => 'palais-royale-worli',
            'name' => 'Palais Royale',
            'city' => 'Mumbai',
            'area' => 'Worli',
            'type' => 'Apartment',
            'status' => 'Under Construction',
            'bhk' => '4, 5 BHK',
            'min_price' => 120000000,
            'max_price' => 350000000,
            'size_sqft' => 4200,
            'builtup_min' => 3800,
            'builtup_max' => 7800,
            'images' => $img('public/images/projects/mumbai-tower-2.jpg', 'public/images/projects/luxury-lobby.jpg', 'public/images/projects/mumbai-interior-1.jpg'),
            'videos' => ['public/videos/meridian-tour.mp4'],
            'featured' => 0,
            'sort_order' => 6,
            'builder' => 'Shree Ram Urban Infrastructure — ultra-luxury Worli development.',
            'overview' => 'An ultra-luxury Worli tower planned around large residences, high ceilings and private living floors.',
            'amenities' => ['Private elevator lobby', 'Club lounge', 'Pool deck', 'Security', 'Parking'],
            'construction_status' => 'Under Construction',
            'construction_progress' => 82,
            'possession_date' => '2027-12-01',
            'launch_date' => '2008-01-01',
            'rera' => 'P51900006789',
            'latitude' => 19.0072,
            'longitude' => 72.8158,
        ],
        [
            'slug' => 'oberoi-sky-city',
            'name' => 'Oberoi Sky City',
            'city' => 'Mumbai',
            'area' => 'Borivali East',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '2, 3 BHK',
            'min_price' => 28000000,
            'max_price' => 65000000,
            'size_sqft' => 1180,
            'builtup_min' => 950,
            'builtup_max' => 1850,
            'images' => $img('public/images/projects/mumbai-tower-1.jpg', 'public/images/projects/pool-amenity.jpg', 'public/images/projects/mumbai-tower-2.jpg'),
            'videos' => ['public/videos/foundry-offices-tour.mp4'],
            'featured' => 1,
            'sort_order' => 7,
            'builder' => 'Oberoi Realty — premium residential and mixed-use developer in Mumbai.',
            'overview' => 'A large Borivali residential campus with towers, clubhouse living and Western Express Highway access.',
            'amenities' => ['Clubhouse', 'Swimming pool', 'Landscaped podium', 'Gym', 'Children play area'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2023-03-01',
            'launch_date' => '2015-09-01',
            'rera' => 'P51800007890',
            'latitude' => 19.2245,
            'longitude' => 72.8652,
        ],
        [
            'slug' => 'hiranandani-gardens-powai',
            'name' => 'Hiranandani Gardens',
            'city' => 'Mumbai',
            'area' => 'Powai',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '2, 3, 4 BHK',
            'min_price' => 32000000,
            'max_price' => 90000000,
            'size_sqft' => 1350,
            'builtup_min' => 1100,
            'builtup_max' => 2800,
            'images' => $img('public/images/projects/mumbai-interior-1.jpg', 'public/images/projects/mumbai-tower-2.jpg', 'public/images/projects/pool-amenity.jpg'),
            'videos' => ['public/videos/arbor-tour.mp4'],
            'featured' => 0,
            'sort_order' => 8,
            'builder' => 'Hiranandani Communities — master-planned residential townships.',
            'overview' => 'An established Powai address known for landscaped streets, retail and a full residential community.',
            'amenities' => ['Clubhouse', 'Lake views', 'Retail street', 'Sports facilities', 'Security'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2020-01-01',
            'launch_date' => '1992-01-01',
            'rera' => 'P51800008901',
            'latitude' => 19.1197,
            'longitude' => 72.9051,
        ],

        // Gurgaon
        [
            'slug' => 'dlf-the-camellias',
            'name' => 'DLF The Camellias',
            'city' => 'Gurgaon',
            'area' => 'Golf Course Road',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '4, 5 BHK',
            'min_price' => 180000000,
            'max_price' => 450000000,
            'size_sqft' => 6800,
            'builtup_min' => 5200,
            'builtup_max' => 9200,
            'images' => $img('public/images/projects/gurgaon-tower-1.jpg', 'public/images/projects/gurgaon-interior-1.jpg', 'public/images/projects/luxury-lobby.jpg'),
            'videos' => ['public/videos/horizon-tour.mp4'],
            'featured' => 1,
            'sort_order' => 9,
            'builder' => 'DLF Limited — India’s largest publicly listed real-estate company.',
            'overview' => 'Ultra-luxury residences on Golf Course Road with expansive apartments and private club living.',
            'amenities' => ['Private club', 'Concierge', 'Spa', 'Pool', 'Valet parking'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2019-06-01',
            'launch_date' => '2012-01-01',
            'rera' => 'GGM/123/2017',
            'latitude' => 28.4431,
            'longitude' => 77.1025,
        ],
        [
            'slug' => 'm3m-golf-estate',
            'name' => 'M3M Golf Estate',
            'city' => 'Gurgaon',
            'area' => 'Sector 65',
            'type' => 'Apartment',
            'status' => 'Ready',
            'bhk' => '3, 4 BHK',
            'min_price' => 45000000,
            'max_price' => 120000000,
            'size_sqft' => 2850,
            'builtup_min' => 2400,
            'builtup_max' => 5200,
            'images' => $img('public/images/projects/gurgaon-tower-2.jpg', 'public/images/projects/pool-amenity.jpg', 'public/images/projects/gurgaon-interior-1.jpg'),
            'videos' => ['public/videos/verdant-villas-tour.mp4'],
            'featured' => 1,
            'sort_order' => 10,
            'builder' => 'M3M India — Gurgaon-focused luxury residential and commercial developer.',
            'overview' => 'Golf-facing residences in Sector 65 with large floor plates and resort-style amenities.',
            'amenities' => ['Golf course views', 'Clubhouse', 'Swimming pool', 'Gym', 'Landscaped greens'],
            'construction_status' => 'Ready',
            'construction_progress' => 100,
            'possession_date' => '2022-08-01',
            'launch_date' => '2013-04-01',
            'rera' => 'GGM/456/2017',
            'latitude' => 28.4028,
            'longitude' => 77.0689,
        ],
        [
            'slug' => 'emaar-digihomes',
            'name' => 'Emaar DigiHomes',
            'city' => 'Gurgaon',
            'area' => 'Sector 62',
            'type' => 'Apartment',
            'status' => 'Under Construction',
            'bhk' => '2, 3 BHK',
            'min_price' => 18500000,
            'max_price' => 32000000,
            'size_sqft' => 1320,
            'builtup_min' => 1050,
            'builtup_max' => 1750,
            'images' => $img('public/images/projects/gurgaon-interior-1.jpg', 'public/images/projects/gurgaon-tower-1.jpg', 'public/images/projects/skyline-dusk.jpg'),
            'videos' => ['public/videos/foundry-offices-tour.mp4'],
            'featured' => 0,
            'sort_order' => 11,
            'builder' => 'Emaar India — residential communities across the NCR.',
            'overview' => 'Smart-home ready residences near Golf Course Extension Road with contemporary tower living.',
            'amenities' => ['Smart home features', 'Clubhouse', 'Pool', 'Coworking lounge', 'Parking'],
            'construction_status' => 'Under Construction',
            'construction_progress' => 58,
            'possession_date' => '2027-06-01',
            'launch_date' => '2021-02-01',
            'rera' => 'GGM/789/2021',
            'latitude' => 28.4126,
            'longitude' => 77.0884,
        ],
        [
            'slug' => 'godrej-meridien',
            'name' => 'Godrej Meridien',
            'city' => 'Gurgaon',
            'area' => 'Sector 106',
            'type' => 'Apartment',
            'status' => 'New Launch',
            'bhk' => '2, 3 BHK',
            'min_price' => 16500000,
            'max_price' => 28500000,
            'size_sqft' => 1240,
            'builtup_min' => 980,
            'builtup_max' => 1680,
            'images' => $img('public/images/projects/gurgaon-tower-2.jpg', 'public/images/projects/gurgaon-tower-1.jpg', 'public/images/projects/gurgaon-interior-1.jpg'),
            'videos' => ['public/videos/horizon-tour.mp4'],
            'featured' => 0,
            'sort_order' => 12,
            'builder' => 'Godrej Properties — national developer with a strong Gurgaon portfolio.',
            'overview' => 'A Dwarka Expressway-facing residence with landscaped greens and efficient apartment plans.',
            'amenities' => ['Clubhouse', 'Landscaped gardens', 'Gym', 'Kids play', 'Power backup'],
            'construction_status' => 'New Launch',
            'construction_progress' => 35,
            'possession_date' => '2028-03-01',
            'launch_date' => '2023-09-01',
            'rera' => 'GGM/901/2023',
            'latitude' => 28.5019,
            'longitude' => 76.9954,
        ],
    ];

    $keepSlugs = array_column($samples, 'slug');

    $findProperty = $pdo->prepare('SELECT id FROM properties WHERE slug = :slug LIMIT 1');
    $insertProperty = $pdo->prepare(
        'INSERT INTO properties
            (name, slug, city_id, area_id, property_type, property_status, bhk, min_price, max_price,
             address, overview, amenities, possession_date, latitude, longitude, is_featured, is_active,
             sort_order, publication_status, construction_status, construction_progress, launch_date,
             builtup_min_sqft, builtup_max_sqft, about_builder, seo_title, seo_description, rera_number)
         VALUES
            (:name, :slug, :city_id, :area_id, :property_type, :property_status, :bhk, :min_price, :max_price,
             :address, :overview, :amenities, :possession_date, :latitude, :longitude, :is_featured, 1,
             :sort_order, :publication_status, :construction_status, :construction_progress, :launch_date,
             :builtup_min_sqft, :builtup_max_sqft, :about_builder, :seo_title, :seo_description, :rera_number)'
    );
    $updateProperty = $pdo->prepare(
        'UPDATE properties SET
            name = :name,
            city_id = :city_id,
            area_id = :area_id,
            property_type = :property_type,
            property_status = :property_status,
            bhk = :bhk,
            min_price = :min_price,
            max_price = :max_price,
            address = :address,
            overview = :overview,
            amenities = :amenities,
            possession_date = :possession_date,
            latitude = :latitude,
            longitude = :longitude,
            is_featured = :is_featured,
            is_active = 1,
            deleted_at = NULL,
            sort_order = :sort_order,
            publication_status = :publication_status,
            construction_status = :construction_status,
            construction_progress = :construction_progress,
            launch_date = :launch_date,
            builtup_min_sqft = :builtup_min_sqft,
            builtup_max_sqft = :builtup_max_sqft,
            about_builder = :about_builder,
            seo_title = :seo_title,
            seo_description = :seo_description,
            rera_number = :rera_number
         WHERE id = :id'
    );

    foreach ($samples as $sample) {
        if (!isset($cityIds[$sample['city']])) {
            throw new RuntimeException('Missing city: ' . $sample['city']);
        }

        $cityId = $cityIds[$sample['city']];
        $areaId = $resolveArea($cityId, $sample['area']);
        $amenitiesText = implode(', ', $sample['amenities']);
        $payload = [
            'name' => $sample['name'],
            'slug' => $sample['slug'],
            'city_id' => $cityId,
            'area_id' => $areaId,
            'property_type' => $sample['type'],
            'property_status' => $sample['status'],
            'bhk' => $sample['bhk'],
            'min_price' => $sample['min_price'],
            'max_price' => $sample['max_price'],
            'address' => $sample['area'] . ', ' . $sample['city'],
            'overview' => $sample['overview'],
            'amenities' => $amenitiesText,
            'possession_date' => $sample['possession_date'],
            'latitude' => $sample['latitude'],
            'longitude' => $sample['longitude'],
            'is_featured' => $sample['featured'],
            'sort_order' => $sample['sort_order'],
            'publication_status' => 'Published',
            'construction_status' => $sample['construction_status'],
            'construction_progress' => $sample['construction_progress'],
            'launch_date' => $sample['launch_date'],
            'builtup_min_sqft' => $sample['builtup_min'],
            'builtup_max_sqft' => $sample['builtup_max'],
            'about_builder' => $sample['builder'],
            'seo_title' => $sample['name'] . ' | ' . $sample['area'] . ' | PropGemini',
            'seo_description' => $sample['overview'],
            'rera_number' => $sample['rera'],
        ];

        $findProperty->execute(['slug' => $sample['slug']]);
        $existingId = $findProperty->fetchColumn();

        if ($existingId === false) {
            $insertProperty->execute($payload);
            $propertyId = (int) $pdo->lastInsertId();
        } else {
            $propertyId = (int) $existingId;
            $payload['id'] = $propertyId;
            unset($payload['slug']);
            $updateProperty->execute($payload);
            $pdo->prepare('DELETE FROM property_images WHERE property_id = ?')->execute([$propertyId]);
            $pdo->prepare('DELETE FROM property_videos WHERE property_id = ?')->execute([$propertyId]);
            $pdo->prepare('DELETE FROM property_amenities WHERE property_id = ?')->execute([$propertyId]);
            $pdo->prepare('DELETE FROM property_unit_types WHERE property_id = ?')->execute([$propertyId]);
        }

        $imageInsert = $pdo->prepare(
            'INSERT INTO property_images (property_id, image_path, sort_order, media_type)
             VALUES (?, ?, ?, \'image\')'
        );
        foreach ($sample['images'] as $index => $imagePath) {
            $imageInsert->execute([$propertyId, $imagePath, $index]);
        }

        $videoInsert = $pdo->prepare(
            'INSERT INTO property_videos (property_id, video_path, sort_order)
             VALUES (?, ?, ?)'
        );
        foreach ($sample['videos'] as $index => $videoPath) {
            $videoInsert->execute([$propertyId, $videoPath, $index]);
        }

        $amenityInsert = $pdo->prepare(
            'INSERT INTO property_amenities (property_id, amenity_name) VALUES (?, ?)'
        );
        foreach ($sample['amenities'] as $amenity) {
            $amenityInsert->execute([$propertyId, $amenity]);
        }

        $pdo->prepare(
            'INSERT INTO property_unit_types
                (property_id, configuration, carpet_area_sqft, price, description, is_available, sort_order)
             VALUES (?, ?, ?, ?, ?, 1, 0)'
        )->execute([
            $propertyId,
            $sample['bhk'],
            $sample['size_sqft'],
            $sample['min_price'],
            $sample['overview'],
        ]);

        echo "Seeded {$sample['slug']} (#{$propertyId})\n";
    }

    $placeholders = implode(',', array_fill(0, count($keepSlugs), '?'));
    $softDelete = $pdo->prepare(
        "UPDATE properties
         SET deleted_at = NOW(), is_active = 0, publication_status = 'Unpublished', is_featured = 0
         WHERE deleted_at IS NULL
           AND slug NOT IN ({$placeholders})"
    );
    $softDelete->execute($keepSlugs);
    echo 'Archived older demo listings: ' . $softDelete->rowCount() . PHP_EOL;

    $settings = [
        'whatsapp_number' => '919323443610',
        'contact_phone' => '+91 93234 43610',
        'contact_email' => 'info@propgemini.com',
        'contact_address' => 'Pune · Mumbai · Gurgaon',
        'about_blurb' => 'PropGemini curates premium residences across India’s key cities.',
        'emi_default_loan' => '5000000',
        'emi_default_rate' => '8.5',
        'emi_default_years' => '20',
    ];
    $settingUpsert = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value)
         VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    foreach ($settings as $key => $value) {
        $settingUpsert->execute(['key' => $key, 'value' => $value]);
    }

    $pdo->commit();
    echo "DONE — seeded " . count($samples) . " landmark projects\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'SEED_FAIL: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
