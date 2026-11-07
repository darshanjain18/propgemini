<?php
declare(strict_types=1);

$urls = [
    '/pages/properties',
    '/pages/property?id=horizon',
    '/admin',
    '/admin/dashboard',
    '/admin/properties',
];

foreach ($urls as $u) {
    $headers = @get_headers('http://127.0.0.1:8000' . $u);
    echo $u . ' => ' . ($headers[0] ?? 'fail') . PHP_EOL;
}
