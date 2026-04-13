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

\Elgg\Application::start();

// Plugin's own autoloader.php is loaded by Elgg's plugin activation path.
// Ensure it ran by activating the plugin if needed.
if (!function_exists('hypeFilestore')) {
    require_once dirname(__DIR__) . '/lib/autoloader.php';
}
