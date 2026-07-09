<?php

namespace hypeJunction\Filestore\Config;

use Elgg\IntegrationTestCase;

/**
 * Integration coverage for the hypeFilestore Config service on Elgg 7.x.
 *
 * Config reads plugin settings through a real ElggPlugin, so these run against
 * a booted engine. They pin the documented default configuration and the
 * icon-size tables the icon factory depends on.
 */
class ConfigTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		// The plugin is active in the production DB but the snapshot may lag;
		// skip the base-class active check and guard explicitly below.
		return '';
	}

	protected function getPlugin(): \ElggPlugin {
		$plugin = elgg_get_plugin_from_id('hypefilestore');
		if (!$plugin) {
			$this->markTestSkipped('hypefilestore plugin not installed in test DB');
		}
		return $plugin;
	}

	public function testDefaultPrefixesAndSizeConstants() {
		$config = new Config($this->getPlugin());
		$this->assertSame('hypefilestore', Config::PLUGIN_ID);
		$this->assertSame('file', $config->getDefaultFilestorePrefix());
		$this->assertSame('icons', $config->getDefaultIconDirectory());
		$this->assertSame('medium', Config::SIZE_MEDIUM);
		$this->assertSame('master', Config::SIZE_MASTER);
	}

	public function testCroppableSizesExactSet() {
		$config = new Config($this->getPlugin());
		$this->assertSame(
			['large', 'medium', 'small', 'tiny', 'topbar'],
			$config->getCroppableSizes()
		);
	}

	public function testFileIconSizesStructure() {
		$config = new Config($this->getPlugin());
		$sizes = $config->getFileIconSizes();

		$this->assertSame([60, 60], [$sizes['small']['w'], $sizes['small']['h']]);
		$this->assertSame('thumbnail', $sizes['small']['metadata_name']);
		$this->assertTrue($sizes['small']['square']);
		$this->assertTrue($sizes['small']['upscale']);

		$this->assertSame([153, 153], [$sizes['medium']['w'], $sizes['medium']['h']]);
		$this->assertSame('smallthumb', $sizes['medium']['metadata_name']);

		$this->assertSame([600, 600], [$sizes['large']['w'], $sizes['large']['h']]);
		$this->assertSame('largethumb', $sizes['large']['metadata_name']);
	}
}
