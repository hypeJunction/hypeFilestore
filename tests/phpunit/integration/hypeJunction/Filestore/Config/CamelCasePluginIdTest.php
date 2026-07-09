<?php

namespace hypeJunction\Filestore\Config;

use Elgg\IntegrationTestCase;

/**
 * Runtime proof that the camelCase plugin-id fallback in Config::factory()
 * (elgg-migrate residue: Config.php:33) is dead on Elgg 7.
 *
 * Elgg stores plugin ids lowercase and elgg_get_plugin_from_id() matches the
 * stored id verbatim, so 'hypeFilestore' can never resolve — the `?:` fallback
 * always yields null. Config::factory() therefore only ever builds from the
 * lowercase Config::PLUGIN_ID lookup.
 */
class CamelCasePluginIdTest extends IntegrationTestCase {

	public function up() {}

	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testCamelCasePluginIdFallbackIsDeadOnElgg7() {
		$lower = elgg_get_plugin_from_id('hypefilestore');
		if (!$lower) {
			$this->markTestSkipped('hypefilestore plugin not installed in test DB');
		}

		// The lowercase id (Config::PLUGIN_ID) is the one that resolves.
		$this->assertInstanceOf(\ElggPlugin::class, $lower);
		$this->assertSame('hypefilestore', Config::PLUGIN_ID);

		// The camelCase fallback can never resolve — ids are stored lowercase.
		$this->assertNull(
			elgg_get_plugin_from_id('hypeFilestore'),
			"elgg_get_plugin_from_id('hypeFilestore') returns null on Elgg 7 — the camelCase fallback in Config::factory() is dead residue"
		);

		// Config::factory() consequently wraps the lowercase-resolved plugin.
		$config = Config::factory();
		$this->assertSame(
			$lower->getPath(),
			$config->getPath(),
			'Config::factory() builds from the lowercase lookup, never the camelCase fallback'
		);
	}
}
