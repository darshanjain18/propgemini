<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/settings.php';

$legal = legalPageContent('privacy');
$pageTitle = $legal['title'] . ' | PropGemini';
$pageDescription = 'How PropGemini handles personal information from property enquiries.';
$bodyClass = 'legal-page';

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/navbar.php';
?>
<main class="legal-main">
    <section class="page-hero">
        <div class="wrap">
            <p class="eyebrow">PropGemini</p>
            <h1><?= h($legal['title']) ?></h1>
            <p>How we handle the information you choose to share with us.</p>
        </div>
    </section>
    <section class="wrap legal-content">
        <?= renderLegalBody($legal['body']) ?>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
