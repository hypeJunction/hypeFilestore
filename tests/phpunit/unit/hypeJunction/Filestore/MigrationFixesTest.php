<?php

namespace hypeJunction\Filestore;

use Elgg\Event;
use Elgg\UnitTestCase;
use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Di\PluginContainer;
use hypeJunction\Filestore\Icons\Factory;
use hypeJunction\Filestore\Listeners\PluginHooks;

/**
 * Regression coverage for the migration fixes applied to hypeFilestore on the
 * way to Elgg 7.x.
 *
 * Each test pins the FIXED shape of one migration commit so a regression that
 * re-introduces the pre-migration code fails here rather than fataling in
 * production. The guards are static (source/config) or exercise pure API
 * surfaces that do not require a booted engine.
 */
class MigrationFixesTest extends UnitTestCase {

	public function up() {}
	public function down() {}

	protected function pluginRoot(): string {
		$dir = __DIR__;
		for ($i = 0; $i < 6; $i++) {
			if (is_file($dir . '/elgg-plugin.php')) {
				return $dir;
			}
			$dir = dirname($dir);
		}
		$this->fail('Could not locate plugin root (elgg-plugin.php)');
	}

	protected function source(string $relative): string {
		$path = $this->pluginRoot() . '/' . ltrim($relative, '/');
		$this->assertFileExists($path);
		return (string) file_get_contents($path);
	}

	protected function mockEvent($value, array $params = []): Event {
		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getValue')->willReturn($value);
		$event->method('getParam')->willReturnCallback(function ($name, $default = null) use ($params) {
			return $params[$name] ?? $default;
		});
		return $event;
	}

	/**
	 * 400e976 — Factory::outputRawIcon snapshots disabled-entity visibility via
	 * elgg()->session->getDisabledEntityVisibility(); the removed 4.x symbol
	 * access_get_show_hidden_status() must be gone.
	 */
	public function testOutputRawIconUsesSessionServiceNotRemovedGetter() {
		$src = $this->source('classes/hypeJunction/Filestore/Icons/Factory.php');
		$this->assertStringContainsString('elgg()->session->getDisabledEntityVisibility()', $src);
		$this->assertDoesNotMatchRegularExpression('/access_get_show_hidden_status\s*\(/', $src);
	}

	/**
	 * 06095d8 — handleEntityIconUrls takes a single \Elgg\Event and reads
	 * value/entity/size off it. When a prior plugin already set a non-null icon
	 * URL it is returned unchanged; when no entity param is present the existing
	 * (null) value is returned. Neither path touches the icon factory.
	 */
	public function testHandleEntityIconUrlsAcceptsElggEvent() {
		$override = 'https://cdn.example.com/preset-icon.png';
		$this->assertSame(
			$override,
			PluginHooks::handleEntityIconUrls($this->mockEvent($override, ['size' => 'medium'])),
			'a non-null existing icon URL must be returned unchanged'
		);

		$this->assertNull(
			PluginHooks::handleEntityIconUrls($this->mockEvent(null, ['size' => 'medium'])),
			'with no entity param the existing (null) value is returned'
		);
	}

	/**
	 * 7a8af80 — Config::PLUGIN_ID is lowercase ('hypefilestore'; camelCase
	 * silently resolves to false from elgg_get_plugin_from_id on 4.x+). The
	 * PluginContainer reports its four services via __isset and throws a
	 * descriptive \RuntimeException for any unknown key.
	 */
	public function testPluginContainerLowercaseIdAndUnknownServiceThrows() {
		$this->assertSame('hypefilestore', Config::PLUGIN_ID);

		$container = PluginContainer::create();
		foreach (['config', 'hooks', 'uploader', 'iconFactory'] as $service) {
			$this->assertTrue(isset($container->$service), "__isset must report '$service'");
		}
		$this->assertFalse(isset($container->bogus), '__isset must be false for unknown keys');

		try {
			$container->bogus;
			$this->fail('accessing an unknown service must throw');
		} catch (\RuntimeException $e) {
			$this->assertStringStartsWith('Undefined service:', $e->getMessage());
		}
	}

	/**
	 * eb1a1c8 — the entity:icon:url handler is registered declaratively via the
	 * elgg-plugin.php 'events' key (not in init() code), and PluginHooks::init()
	 * is a retained no-op kept only for call-surface backwards compatibility.
	 */
	public function testEventRegistrationIsDeclarativeAndInitIsNoOp() {
		$manifest = require $this->pluginRoot() . '/elgg-plugin.php';

		$this->assertArrayHasKey('events', $manifest);
		$this->assertArrayHasKey('entity:icon:url', $manifest['events']);
		$this->assertArrayHasKey('all', $manifest['events']['entity:icon:url']);
		$this->assertArrayHasKey(
			PluginHooks::class . '::handleEntityIconUrls',
			$manifest['events']['entity:icon:url']['all'],
			'entity:icon:url/all must map to PluginHooks::handleEntityIconUrls'
		);

		$config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
		$factory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
		$hooks = new PluginHooks($config, $factory);
		$this->assertNull($hooks->init(), 'init() must remain a no-op returning null');
	}

	/**
	 * b2a7308 — the removed elgg_register_classes custom autoloader
	 * (lib/autoloader.php) was replaced by Composer PSR-4; the hypeFilestore()
	 * factory is loaded via require_once at the top of elgg-plugin.php.
	 */
	public function testComposerAutoloadIsPsr4WithoutLegacyAutoloader() {
		$composer = json_decode($this->source('composer.json'), true);
		$this->assertIsArray($composer);
		$this->assertArrayHasKey('psr-4', $composer['autoload']);
		$this->assertSame(
			'classes/hypeJunction/Filestore/',
			$composer['autoload']['psr-4']['hypeJunction\\Filestore\\']
		);
		$this->assertArrayNotHasKey('psr-0', $composer['autoload']);

		$this->assertFileDoesNotExist(
			$this->pluginRoot() . '/lib/autoloader.php',
			'the legacy elgg_register_classes autoloader must be gone'
		);
		$this->assertStringContainsString(
			"require_once __DIR__ . '/lib/functions.php'",
			$this->source('elgg-plugin.php')
		);
		$this->assertTrue(function_exists('hypeFilestore'));
	}
}
