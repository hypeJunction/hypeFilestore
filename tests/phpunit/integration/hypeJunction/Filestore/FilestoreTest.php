<?php

namespace hypeJunction\Filestore;

use Elgg\Event;
use Elgg\IntegrationTestCase;
use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Handlers\Uploader;
use hypeJunction\Filestore\Icons\Factory;
use hypeJunction\Filestore\Listeners\PluginHooks;

/**
 * Integration tests for hypeFilestore on a booted Elgg 7.x.
 *
 * Covers declarative event registration (elgg-plugin.php actually wires the
 * entity:icon:url handler), the DI container's runtime singleton behaviour, and
 * the icon/cover size resolution that the icon-URL handler relies on.
 */
class FilestoreTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
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
	 * The elgg-plugin.php 'events' key must actually register the icon-URL
	 * handler on a booted engine (not just be present in the manifest array).
	 */
	public function testEntityIconUrlEventHandlerRegistered() {
		$this->assertTrue(
			_elgg_services()->events->hasHandler('entity:icon:url', 'all'),
			'entity:icon:url/all handler must be registered from elgg-plugin.php'
		);
	}

	/**
	 * hypeFilestore() resolves the four services lazily and returns the SAME
	 * instance on repeated access (singleton contract, ref 7a8af80).
	 */
	public function testContainerResolvesFourServicesAsSingletons() {
		$c = hypeFilestore();

		$this->assertInstanceOf(Config::class, $c->config);
		$this->assertInstanceOf(Factory::class, $c->iconFactory);
		$this->assertInstanceOf(PluginHooks::class, $c->hooks);
		$this->assertInstanceOf(Uploader::class, $c->uploader);

		$this->assertSame($c->config, $c->config);
		$this->assertSame($c->iconFactory, $c->iconFactory);
		$this->assertSame($c->hooks, $c->hooks);
		$this->assertSame($c->uploader, $c->uploader);
	}

	/**
	 * When the entity has no 'icontime' private setting the handler returns the
	 * existing (null) value and never builds a signed URL — exercised against a
	 * real user through the real icon factory.
	 */
	public function testHandleEntityIconUrlsReturnsExistingWhenNoIcontime() {
		if (!elgg_get_plugin_from_id('hypefilestore')) {
			$this->markTestSkipped('hypefilestore plugin not installed in test DB');
		}
		$user = $this->createUser();
		$this->assertFalse((bool) $user->getPrivateSetting('icontime'));

		$event = $this->mockEvent(null, ['entity' => $user, 'size' => 'medium']);
		$this->assertNull(PluginHooks::handleEntityIconUrls($event));
	}

	/**
	 * CoverHandler overrides the default square icon set with a single
	 * non-square 'master' size (1000x370, upscaled) — distinct from the square
	 * IconHandler sizes.
	 */
	public function testCoverHandlerMasterSizeIsNonSquare() {
		$user = $this->createUser();

		$method = new \ReflectionMethod(CoverHandler::class, 'getIconSizes');
		$method->setAccessible(true);
		$sizes = $method->invoke(null, $user);

		$this->assertArrayHasKey('master', $sizes);
		$this->assertSame(1000, $sizes['master']['w']);
		$this->assertSame(370, $sizes['master']['h']);
		$this->assertFalse($sizes['master']['square']);
		$this->assertTrue($sizes['master']['upscale']);
	}
}
