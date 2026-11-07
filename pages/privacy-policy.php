<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';
require_once dirname(__DIR__) . '/includes/notifications.php';

$legal = legalPageContent('privacy');
$legalDoc = parseLegalDocument((string) $legal['body']);
$contact = contactDetails();

$pageTitle = $legal['title'] . ' | PropGemini';
$pageDescription = 'How PropGemini handles personal information from property enquiries.';
$bodyClass = 'legal-page legal-privacy';
$pageStyles = ['public/css/legal.css'];
$pageScripts = ['public/js/legal.js'];

$legalPageKey = 'privacy';
$legalSubtitle = 'How we handle the information you choose to share with us.';
$legalHeroImage = 'public/images/projects/pune-tower-1.jpg';
$legalCalloutTitle = 'In short';
$legalCalloutBody = 'We value your privacy. Information you share through enquiries and site visits is used to respond to your request and arrange viewings. We do not sell your personal information.';
$legalContactTitle = 'Questions about your privacy?';
$legalContactLead = 'Email us anytime and we will help with access, correction, or deletion requests where applicable.';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
require dirname(__DIR__) . '/includes/legal-document.php';
require dirname(__DIR__) . '/includes/footer.php';
