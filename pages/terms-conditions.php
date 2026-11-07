<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';
require_once dirname(__DIR__) . '/includes/notifications.php';

$legal = legalPageContent('terms');
$legalDoc = parseLegalDocument((string) $legal['body']);
$contact = contactDetails();

$pageTitle = $legal['title'] . ' | PropGemini';
$pageDescription = 'PropGemini terms and conditions for property enquiries and website use.';
$bodyClass = 'legal-page legal-terms';
$pageStyles = ['public/css/legal.css'];
$pageScripts = ['public/js/legal.js'];

$legalPageKey = 'terms';
$legalSubtitle = 'Terms for using the PropGemini website and making a property enquiry.';
$legalHeroImage = 'public/images/projects/mumbai-tower-1.jpg';
$legalCalloutTitle = 'Please Read Carefully';
$legalCalloutBody = 'These Terms & Conditions govern your use of the PropGemini website and any property enquiry, brochure request, or site-visit request you submit through it. By using this website you agree to these terms.';
$legalContactTitle = 'Questions about these terms?';
$legalContactLead = 'Reach PropGemini by email or through the Enquire page if you need clarification before submitting a request.';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
require dirname(__DIR__) . '/includes/legal-document.php';
require dirname(__DIR__) . '/includes/footer.php';
