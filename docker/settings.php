<?php

/**
 * Elgg settings for Docker environment.
 */

$CONFIG = new \stdClass;

$CONFIG->dbuser = getenv('ELGG_DB_USER') ?: 'elgg';
$CONFIG->dbpass = getenv('ELGG_DB_PASS') ?: 'elgg';
$CONFIG->dbname = getenv('ELGG_DB_NAME') ?: 'elgg';
$CONFIG->dbhost = getenv('ELGG_DB_HOST') ?: 'db';
$CONFIG->dbprefix = 'elgg_';
$CONFIG->dbencoding = 'utf8mb4';

$CONFIG->wwwroot = getenv('ELGG_SITE_URL') ?: 'http://localhost:8080/';
$CONFIG->dataroot = getenv('ELGG_DATA_ROOT') ?: '/var/www/data/';

// Enable caches for realistic performance in E2E tests.
// Disable for development iteration (views/CSS/JS changes take effect immediately).
$CONFIG->simplecache_enabled = true;
$CONFIG->system_cache_enabled = true;

// Boot settings
$CONFIG->installer_running = false;
$CONFIG->boot_complete = false;
