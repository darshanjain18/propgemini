<?php
declare(strict_types=1);

/**
 * Front controller for PHP's built-in server (and a fallback for clean URLs).
 * Start with: php -S 127.0.0.1:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$uri = '/' . trim(str_replace('\\', '/', $uri), '/');
if ($uri === '/') {
    $index = __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($index)) {
        require $index;
        return true;
    }
    return false;
}

$root = realpath(__DIR__);
if ($root === false) {
    http_response_code(500);
    echo 'Server root missing';
    return true;
}

// Clean property URLs: /property/{slug}
if (preg_match('#^/property/([^/]+)/?$#', $uri, $match) === 1) {
    $_GET['id'] = rawurldecode($match[1]);
    require $root . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'property.php';
    return true;
}

// Clean news article URLs: /news/{slug}
if (preg_match('#^/news/([^/]+)/?$#', $uri, $match) === 1) {
    $_GET['slug'] = rawurldecode($match[1]);
    require $root . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'news-article.php';
    return true;
}

$relative = ltrim($uri, '/');
$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

// Serve existing static files directly.
if (is_file($path)) {
    return false;
}

$candidates = [
    $path . '.php',
    $path . DIRECTORY_SEPARATOR . 'index.php',
];

foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if ($real !== false && is_file($real) && str_starts_with($real, $root)) {
        require $real;
        return true;
    }
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo '404 Not Found';
return true;
