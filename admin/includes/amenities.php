<?php
declare(strict_types=1);

function amenityCatalog(): array
{
    return [
        'Lifestyle' => [
            'Clubhouse',
            'Swimming pool',
            'Kids pool',
            'Gymnasium',
            'Yoga / meditation deck',
            'Spa & sauna',
            'Indoor games room',
            'Multipurpose hall',
            'Party lawn',
            'Amphitheatre',
            'Library / reading lounge',
            'Coworking space',
            'Café / convenience store',
        ],
        'Outdoor' => [
            'Landscaped gardens',
            'Jogging track',
            'Cycling track',
            'Children play area',
            'Senior citizen sit-out',
            'Open-air theatre',
            'BBQ / picnic zone',
            'Pet park',
            'Rooftop deck',
            'Private garden',
            'Sky deck',
        ],
        'Sports' => [
            'Badminton court',
            'Tennis court',
            'Basketball court',
            'Squash court',
            'Cricket pitch',
            'Table tennis',
            'Snooker / billiards',
            'Skating rink',
        ],
        'Convenience' => [
            'Covered parking',
            'Visitor parking',
            'EV charging',
            'Power backup',
            '24x7 water supply',
            'Rainwater harvesting',
            'Sewage treatment plant',
            'Waste management',
            'High-speed lifts',
            'Service lifts',
            'Reception lobby',
            'Concierge desk',
        ],
        'Safety' => [
            'CCTV surveillance',
            'Gated security',
            'Intercom',
            'Fire fighting system',
            'Access control',
            'Video door phone',
        ],
        'Home features' => [
            'Modular kitchen',
            'Walk-in wardrobe',
            'Home automation ready',
            'Vastu compliant',
            'Vastu-compliant layouts',
            'Gas pipeline',
            'False ceiling',
            'Premium flooring',
        ],
    ];
}

function flattenAmenityCatalog(): array
{
    $all = [];
    foreach (amenityCatalog() as $items) {
        foreach ($items as $item) {
            $all[$item] = $item;
        }
    }
    return array_values($all);
}
