<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/analytics.php';

$pageTitle = $pageTitle ?? 'PropGemini';
$pageDescription = $pageDescription ?? 'Discover exceptional residential and commercial properties across Pune, Mumbai and Gurgaon with PropGemini, a considered property advisory experience.';
$bodyClass = trim($bodyClass ?? '');
$trackPropertyId = isset($property['db_id']) ? (int) $property['db_id'] : null;

recordSiteTraffic(null, (string) $pageTitle, $trackPropertyId && $trackPropertyId > 0 ? $trackPropertyId : null);
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= h($pageDescription) ?>">
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($pageDescription) ?>">
<title><?= h($pageTitle) ?></title>
<link rel="icon" href="<?= h(webPath('public/icons/favicon.png')) ?>" type="image/png" sizes="512x512">
<link rel="apple-touch-icon" href="<?= h(webPath('public/icons/apple-touch-icon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(webPath('public/css/style.css')) ?>">
<link rel="stylesheet" href="<?= h(webPath('public/css/design-system.css')) ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
    <?php foreach ($pageStyles as $styleHref): ?>
        <link rel="stylesheet" href="<?= h(webPath((string) $styleHref)) ?>">
    <?php endforeach; ?>
<?php endif; ?>
</head>
<body<?= $bodyClass !== '' ? ' class="' . h($bodyClass) . '"' : '' ?>>
