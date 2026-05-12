<?php

$elgg_root = getenv('ELGG_ROOT') ?: '/var/www/html';
if (!file_exists($elgg_root . '/vendor/autoload.php')) {
    throw new \RuntimeException("Elgg not found at $elgg_root. Run tests inside the Docker container.");
}
require_once $elgg_root . '/vendor/autoload.php';

$plugin_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($plugin_autoload)) {
    require_once $plugin_autoload;
}
