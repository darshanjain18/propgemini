<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'PropGemini | Exceptional Property. Considered Living.';
$pageDescription = 'Discover exceptional residential and commercial properties across Pune, Mumbai and Gurgaon with PropGemini, a considered property advisory experience.';
$bodyClass = 'home';
$pageStyles = ['public/css/home-hero.css', 'public/css/home-amenities.css', 'public/css/home-why.css', 'public/css/home-properties.css', 'public/css/home-cities.css', 'public/css/home-path.css'];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
require __DIR__ . '/includes/home.php';
require __DIR__ . '/includes/footer.php';
