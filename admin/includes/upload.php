<?php
declare(strict_types=1);

function ensureUploadDir(string $subdir): string
{
    $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subdir;
    if (!is_dir($root)) {
        mkdir($root, 0755, true);
    }
    return $root;
}

function storeUploadedFile(array $file, string $subdir, array $allowedMimes, int $maxBytes): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }

    if (($file['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('File is too large.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Unsupported file type.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        default => pathinfo((string) $file['name'], PATHINFO_EXTENSION) ?: 'bin',
    };

    $dir = ensureUploadDir($subdir);
    $name = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    $dest = $dir . DIRECTORY_SEPARATOR . $name;

    if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    return 'public/uploads/' . trim($subdir, '/') . '/' . $name;
}

function resizePropertyImage(string $absolutePath, string $mime, int $maxWidth = 1920, int $maxHeight = 1920, int $quality = 82): void
{
    if (!extension_loaded('gd')) {
        return;
    }

    $info = @getimagesize($absolutePath);
    if ($info === false) {
        return;
    }

    [$width, $height] = $info;
    if ($width <= 0 || $height <= 0) {
        return;
    }

    $scale = min($maxWidth / $width, $maxHeight / $height, 1.0);
    $newW = max(1, (int) round($width * $scale));
    $newH = max(1, (int) round($height * $scale));

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($absolutePath),
        'image/png' => @imagecreatefrompng($absolutePath),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
        'image/gif' => @imagecreatefromgif($absolutePath),
        default => false,
    };
    if ($src === false) {
        return;
    }

    $dst = imagecreatetruecolor($newW, $newH);
    if ($dst === false) {
        imagedestroy($src);
        return;
    }

    if (in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);

    // Prefer JPEG for photos to keep gallery weight down; keep PNG/WebP when alpha matters.
    if ($mime === 'image/png' || $mime === 'image/webp') {
        if ($mime === 'image/webp' && function_exists('imagewebp')) {
            imagewebp($dst, $absolutePath, $quality);
        } else {
            imagepng($dst, $absolutePath, 6);
        }
    } elseif ($mime === 'image/gif') {
        imagegif($dst, $absolutePath);
    } else {
        imagejpeg($dst, $absolutePath, $quality);
    }

    imagedestroy($src);
    imagedestroy($dst);
}

function storeImageUpload(array $file): string
{
    $path = storeUploadedFile(
        $file,
        'properties',
        ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        8 * 1024 * 1024
    );

    $absolute = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($absolute) ?: 'image/jpeg';
    resizePropertyImage($absolute, $mime);

    return $path;
}

function storeBrochureUpload(array $file): string
{
    return storeUploadedFile(
        $file,
        'brochures',
        ['application/pdf'],
        15 * 1024 * 1024
    );
}

function storeFloorPlanUpload(array $file): string
{
    return storeUploadedFile(
        $file,
        'floorplans',
        ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
        8 * 1024 * 1024
    );
}

function storeVideoUpload(array $file): string
{
    return storeUploadedFile(
        $file,
        'properties',
        ['video/mp4', 'video/webm', 'video/quicktime'],
        80 * 1024 * 1024
    );
}
