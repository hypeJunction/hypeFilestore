<?php
/**
 * PHPUnit bootstrap for hypeFilestore plugin tests (Elgg 7.x).
 * Plugin must be installed at {elgg_root}/mod/hypefilestore/
 */

// tests/ -> mod/hypefilestore/ -> mod/ -> elgg_root/
$elggRoot = dirname(__DIR__, 3);

require_once $elggRoot . '/vendor/autoload.php';

// Load Elgg test base classes (UnitTestCase, IntegrationTestCase, etc.)
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$pluginRoot = dirname(__DIR__);

// Plugin PSR-4 autoload (3.x+ replacement for the removed elgg_register_classes
// autoloader). Present once the plugin's own composer deps are installed.
if (file_exists($pluginRoot . '/vendor/autoload.php')) {
    require_once $pluginRoot . '/vendor/autoload.php';
}

// hypeFilestore() DI-container factory lives in lib/functions.php (Iron Law 5:
// elgg-plugin.php cannot hold closures). Load it so unit tests can call it even
// when the plugin is not marked active in the snapshot DB.
if (file_exists($pluginRoot . '/lib/functions.php')) {
    require_once $pluginRoot . '/lib/functions.php';
}

// Register hypeJunction\Filestore classes manually in case the plugin's own
// vendor/autoload is absent and it is not active in the test DB.
spl_autoload_register(function ($class) use ($pluginRoot) {
    if (strncmp($class, 'hypeJunction\\', 13) !== 0) {
        return;
    }
    $file = $pluginRoot . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\Elgg\Application::loadCore();
