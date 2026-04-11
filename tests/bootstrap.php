<?php

/**
 * Bootstrap for elgg_tokeninput plugin tests.
 */

$elggRoot = dirname(dirname(dirname(__DIR__)));
require_once $elggRoot . '/vendor/autoload.php';

// Autoload Elgg test base classes
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require_once $file;
});

// Load plugin autoloader
require_once dirname(__DIR__) . '/autoloader.php';

\Elgg\Application::loadCore();
