<?php
/**
 * PHPUnit bootstrap for hypeFilestore pre-migration smoke tests (Elgg 2.x).
 *
 * Elgg 2.x has no IntegrationTestCase — we boot Elgg manually and run plain
 * PHPUnit\Framework\TestCase against the booted state. Plugin must already be
 * activated in the elgg2 Docker environment.
 */

$elggRoot = '/var/www/html';

require_once $elggRoot . '/vendor/autoload.php';

// Elgg 3.x boot pattern: getInstance() + bootCore() (Elgg 2.x used start()).
// Both branches keep the test class itself version-agnostic.
if (method_exists(\Elgg\Application::class, 'getInstance')) {
    \Elgg\Application::getInstance()->bootCore();
    if (function_exists('_elgg_services')) {
        _elgg_services()->plugins->generateEntities();
        // Force-enable + activate the plugin under test. generateEntities()
        // can leave the entity in a disabled state on first registration,
        // and we need it active for the smoke tests to assert anything.
        $p = elgg_get_plugin_from_id('hypefilestore') ?: elgg_get_plugin_from_id('hypefilestore');
        if ($p) {
            if (!$p->isEnabled()) {
                $p->enable();
            }
            if (!$p->isActive()) {
                $p->activate();
            }
        }
    }
} else {
    \Elgg\Application::start();
}

// Ensure plugin's hypeFilestore() factory is loaded.
// In 4.x: lib/functions.php (loaded via require_once at top of elgg-plugin.php).
// In 2.x: lib/autoloader.php (legacy location, replaced in 3→4).
$pluginRoot = dirname(__DIR__);
if (!function_exists('hypeFilestore')) {
    if (file_exists($pluginRoot . '/lib/functions.php')) {
        require_once $pluginRoot . '/lib/functions.php';
    } elseif (file_exists($pluginRoot . '/lib/autoloader.php')) {
        require_once $pluginRoot . '/lib/autoloader.php';
    }
}

// PSR-4 autoload (3.x+ replacement for elgg_register_classes)
if (file_exists($pluginRoot . '/vendor/autoload.php')) {
    require_once $pluginRoot . '/vendor/autoload.php';
}
