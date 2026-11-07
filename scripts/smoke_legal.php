<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/settings.php';

$terms = legalPageContent('terms');
$privacy = legalPageContent('privacy');

echo 'terms_title=' . $terms['title'] . PHP_EOL;
echo 'terms_body_len=' . strlen($terms['body']) . PHP_EOL;
echo 'privacy_title=' . $privacy['title'] . PHP_EOL;
echo 'privacy_body_len=' . strlen($privacy['body']) . PHP_EOL;
echo 'html_sample=' . substr(strip_tags(renderLegalBody($terms['body'])), 0, 80) . PHP_EOL;
echo "OK\n";
