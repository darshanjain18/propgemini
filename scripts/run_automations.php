<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/automations.php';

$summary = runScheduledAutomations();
echo "visit_reminders={$summary['visit_reminders']}\n";
echo "stale_leads={$summary['stale_leads']}\n";
echo "rera_alerts={$summary['rera_alerts']}\n";
echo "featured_rotated={$summary['featured_rotated']}\n";
echo 'sitemap=' . ($summary['sitemap'] ? 'ok' : 'fail') . "\n";
echo "DONE\n";
