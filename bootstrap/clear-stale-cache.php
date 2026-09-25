<?php

/**
 * Drop stale config/route caches after an SFTP deploy.
 * DEPLOY_REVISION is written last by CI so this only runs once files are in place.
 */
$cacheDir = __DIR__.'/cache';
$revisionFile = dirname(__DIR__).'/DEPLOY_REVISION';
$markerFile = $cacheDir.'/DEPLOY_REVISION';

if (! is_readable($revisionFile)) {
    return;
}

$revision = (string) file_get_contents($revisionFile);
$marker = is_readable($markerFile) ? (string) file_get_contents($markerFile) : '';

if ($revision === '' || $revision === $marker) {
    return;
}

foreach (['config.php', 'routes.php', 'routes-v7.php', 'events.php'] as $cached) {
    $path = $cacheDir.'/'.$cached;
    if (is_file($path)) {
        @unlink($path);
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

if (is_dir($cacheDir) && is_writable($cacheDir)) {
    @file_put_contents($markerFile, $revision);
}
