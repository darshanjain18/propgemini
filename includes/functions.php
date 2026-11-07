<?php
declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function projectWebRoot(): string
{
    $projectFs = realpath(dirname(__DIR__));
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';

    if ($projectFs !== false) {
        $projectFs = str_replace('\\', '/', $projectFs);
        $docRoot = str_replace('\\', '/', $docRoot);

        if ($docRoot !== '' && str_starts_with($projectFs, $docRoot)) {
            $web = substr($projectFs, strlen($docRoot));
            return ($web === false || $web === '') ? '' : rtrim($web, '/');
        }
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $directory = rtrim(str_replace('\\', '/', dirname($script)), '/');

    while (
        preg_match('#/(pages|actions|admin)$#', $directory) === 1
        || preg_match('#/admin/[^/]+$#', $directory) === 1
    ) {
        $directory = rtrim(str_replace('\\', '/', dirname($directory)), '/');
    }

    if ($directory === '' || $directory === '.' || $directory === '/') {
        return '';
    }

    return $directory;
}

function prettyPath(string $path): string
{
    $path = str_replace('\\', '/', $path);
    $query = '';
    if (str_contains($path, '?')) {
        [$path, $query] = explode('?', $path, 2);
        $query = '?' . $query;
    }

    // Keep static assets untouched.
    if (preg_match('/\.(css|js|map|jpg|jpeg|png|gif|webp|svg|ico|mp4|webm|pdf|woff2?|ttf|eot)$/i', $path) === 1) {
        return ltrim($path, '/') . $query;
    }

    $path = preg_replace('/\.php$/i', '', $path) ?? $path;
    $path = preg_replace('#(^|/)index$#i', '$1', $path) ?? $path;
    $path = trim($path, '/');

    return $path . $query;
}

function webPath(string $path = ''): string
{
    $root = projectWebRoot();
    $path = ltrim(str_replace('\\', '/', $path), '/');

    if ($path === '') {
        return $root === '' ? '/' : $root . '/';
    }

    $pretty = prettyPath($path);
    if ($pretty === '' || $pretty === '?') {
        return $root === '' ? '/' : $root . '/';
    }

    return ($root === '' ? '' : $root) . '/' . $pretty;
}

function sectionUrl(string $sectionId): string
{
    $sectionId = ltrim($sectionId, '#');
    $script = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? 'index.php'));

    if ($script === 'index.php' || $script === 'index') {
        return '#' . $sectionId;
    }

    return webPath() . '#' . $sectionId;
}

function renderPublicPageHero(string $kicker, string $title, string $lead = '', string $emphasis = ''): void
{
    $title = trim($title);
    $emphasis = trim($emphasis);
    ?>
    <section class="pg-hero">
        <div class="pg-hero-inner">
            <p class="pg-kicker"><?= h($kicker) ?></p>
            <h1><?= h($title) ?><?php if ($emphasis !== ''): ?> <em><?= h($emphasis) ?></em><?php endif; ?></h1>
            <?php if (trim($lead) !== ''): ?>
                <p><?= h($lead) ?></p>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

function navItems(): array
{
    return [
        ['label' => 'Home', 'href' => webPath()],
        ['label' => 'Projects', 'href' => webPath('pages/properties')],
        ['label' => 'About', 'href' => webPath('pages/about')],
        ['label' => 'News', 'href' => webPath('pages/news')],
        ['label' => 'Gallery', 'href' => webPath('pages/gallery')],
        ['label' => 'EMI Calculator', 'href' => webPath('pages/emi-calculator')],
        ['label' => 'Contact', 'href' => webPath('pages/contact')],
        ['label' => 'Enquire Now', 'href' => webPath('pages/enquire'), 'cta' => true],
    ];
}
