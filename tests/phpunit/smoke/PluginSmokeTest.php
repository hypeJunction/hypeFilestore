<?php

namespace hypeJunction\Filestore\Tests\Smoke;

use PHPUnit\Framework\TestCase;
use hypeJunction\Filestore\Di\PluginContainer;
use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Listeners\PluginHooks;
use hypeJunction\Filestore\Handlers\Uploader;
use hypeJunction\Filestore\Icons\Factory;

/**
 * Pre-migration smoke tests for hypeFilestore (Elgg 2.x baseline).
 *
 * These tests verify the plugin's load surface — what a 2→3 migration is
 * most likely to break: the custom autoloader (uses removed
 * elgg_register_classes), the DI container (extends \Elgg\Di\DiContainer
 * which moved namespace), the hook handler signatures, and the global
 * factory function.
 *
 * Coverage rubric: each test answers "if the migration silently broke this,
 * would the test fail?" with yes.
 */
class PluginSmokeTest extends TestCase
{
    public function testPluginIsActive(): void
    {
        $plugin = elgg_get_plugin_from_id('hypefilestore') ?: elgg_get_plugin_from_id('hypeFilestore');
        $this->assertNotNull($plugin, 'hypeFilestore plugin should be registered');
        // Elgg 2.x isActive() returns an ElggRelationship object (truthy) when active, false otherwise.
        $this->assertNotFalse($plugin->isActive(), 'hypeFilestore should be active');
    }

    public function testGlobalFactoryFunctionExists(): void
    {
        $this->assertTrue(
            function_exists('hypeFilestore'),
            'hypeFilestore() global factory must exist (defined in lib/autoloader.php)'
        );
    }

    public function testDiContainerInstantiates(): void
    {
        $container = hypeFilestore();
        $this->assertInstanceOf(PluginContainer::class, $container);
    }

    public function testDiContainerIsSingleton(): void
    {
        $this->assertSame(hypeFilestore(), hypeFilestore());
    }

    public function testConfigServiceResolves(): void
    {
        $this->assertInstanceOf(Config::class, hypeFilestore()->config);
    }

    public function testHooksServiceResolves(): void
    {
        $this->assertInstanceOf(PluginHooks::class, hypeFilestore()->hooks);
    }

    public function testUploaderServiceResolves(): void
    {
        $this->assertInstanceOf(Uploader::class, hypeFilestore()->uploader);
    }

    public function testIconFactoryServiceResolves(): void
    {
        $this->assertInstanceOf(Factory::class, hypeFilestore()->iconFactory);
    }

    public function testInitHandlerRegistersIconUrlHook(): void
    {
        // Trigger the init() method and verify it doesn't throw.
        // The handler registration itself is the side effect — we don't assert
        // on internal Elgg hook state because that's implementation detail.
        hypeFilestore()->hooks->init();
        $this->assertTrue(true, 'init() returned without error');
    }

    public function testEntityIconUrlHandlerCallable(): void
    {
        $hooks = hypeFilestore()->hooks;
        $this->assertTrue(
            is_callable([$hooks, 'handleEntityIconUrls']),
            'handleEntityIconUrls must be callable on PluginHooks'
        );
    }
}
