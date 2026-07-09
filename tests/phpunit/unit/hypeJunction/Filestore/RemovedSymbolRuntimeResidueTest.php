<?php

namespace hypeJunction\Filestore;

use Elgg\UnitTestCase;

/**
 * Runtime-grounded coverage for the removed-symbol residue that the static
 * MigrationRegressionTest flags but does not prove against the live runtime.
 *
 * Each test pairs two facts:
 *   1. the symbol the plugin still calls no longer exists in the loaded Elgg 7
 *      runtime (so the path fatals when it executes), and
 *   2. the residual callsite is still present in the shipped source.
 *
 * Together they prove these are genuine runtime fatals on 7.x, not stale
 * entries in a hard-coded removed-symbols list. Where the Elgg 7 replacement
 * is a pure service, the expected replacement behaviour is exercised directly.
 */
class RemovedSymbolRuntimeResidueTest extends UnitTestCase {

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

	/**
	 * ElggFile::detectMimeType (static + instance) was removed in Elgg 4.x.
	 * Icons\Factory::create() and getIconFile(), and Uploader\Upload::detectMimeType()
	 * still call it. The Elgg 7 replacement is the mimetype service / ElggFile::getMimeType().
	 */
	public function testDetectMimeTypeRemovedFromRuntimeButStillCalled() {
		$this->assertFalse(
			method_exists(\ElggFile::class, 'detectMimeType'),
			'ElggFile::detectMimeType was removed in Elgg 4.x — every call to it fatals on 7.x'
		);

		// Elgg 7 replacements the callsites should migrate to.
		$this->assertTrue(
			method_exists(\Elgg\Filesystem\MimeTypeService::class, 'getMimeType'),
			'MimeTypeService::getMimeType is the Elgg 7 replacement for the removed static detector'
		);
		$this->assertTrue(
			method_exists(\ElggFile::class, 'getMimeType'),
			'ElggFile::getMimeType is the Elgg 7 replacement for the removed instance detector'
		);

		// Post-migration: the removed static/instance detectMimeType callsites are
		// gone; Factory now resolves mime via the mimetype service and ElggFile::getMimeType().
		$factory = $this->source('classes/hypeJunction/Filestore/Icons/Factory.php');
		$this->assertDoesNotMatchRegularExpression(
			'/(?:ElggFile::|->)detectMimeType\s*\(/',
			$factory,
			'Factory must no longer call removed ElggFile::detectMimeType (static or instance)'
		);
		$this->assertMatchesRegularExpression(
			'/_elgg_services\(\)->mimetype->getMimeType\s*\(/',
			$factory,
			'Factory::create() must resolve mime via the Elgg 7 mimetype service'
		);
		$this->assertMatchesRegularExpression(
			'/->getMimeType\s*\(/',
			$factory,
			'Factory::getIconFile() must use ElggFile::getMimeType() instead of removed detectMimeType'
		);

		$upload = $this->source('classes/hypeJunction/Filestore/Handlers/Uploader/Upload.php');
		$this->assertDoesNotMatchRegularExpression(
			'/ElggFile::detectMimeType\s*\(/',
			$upload,
			'Upload must no longer call removed static ElggFile::detectMimeType'
		);
		$this->assertMatchesRegularExpression(
			'/_elgg_services\(\)->mimetype->getMimeType\s*\(/',
			$upload,
			'Upload::detectMimeType() must resolve mime via the Elgg 7 mimetype service'
		);
	}

	/**
	 * access_show_hidden_entities() was removed in Elgg 4.x. Icons\Factory::outputRawIcon()
	 * — the signed-URL icon-serving endpoint — still calls it twice to toggle visibility.
	 * The plugin already half-adopted the replacement (getDisabledEntityVisibility) but never
	 * swapped the setter for SessionManagerService::setDisabledEntityVisibility().
	 */
	public function testAccessShowHiddenEntitiesRemovedFromRuntimeButStillCalled() {
		$this->assertFalse(
			function_exists('access_show_hidden_entities'),
			'access_show_hidden_entities was removed in Elgg 4.x — the icon-serving path fatals on 7.x'
		);

		$this->assertTrue(
			method_exists(\Elgg\SessionManagerService::class, 'setDisabledEntityVisibility'),
			'SessionManagerService::setDisabledEntityVisibility is the Elgg 7 replacement toggle'
		);

		// Post-migration: the removed save/restore setter is gone; outputRawIcon()
		// now toggles disabled-entity visibility via the session service.
		$factory = $this->source('classes/hypeJunction/Filestore/Icons/Factory.php');
		$this->assertDoesNotMatchRegularExpression(
			'/(?<![\w>$:\\\\])access_show_hidden_entities\s*\(/',
			$factory,
			'outputRawIcon() must no longer call removed access_show_hidden_entities'
		);
		$setterCalls = preg_match_all('/->session->setDisabledEntityVisibility\s*\(/', $factory);
		$this->assertGreaterThanOrEqual(
			2,
			$setterCalls,
			'outputRawIcon() must set and restore visibility via SessionManagerService::setDisabledEntityVisibility'
		);
	}

	/**
	 * elgg_get_file_simple_type() was removed in Elgg 6.x. Uploader\Upload::parseSimpleType()
	 * still calls it to decide the 'image' branch of save(). The Elgg 7 replacement is
	 * MimeTypeService::getSimpleType(), whose classification behaviour is exercised here so
	 * the migrated callsite has a pinned expectation to target.
	 */
	public function testElggGetFileSimpleTypeRemovedFromRuntimeButStillCalled() {
		$this->assertFalse(
			function_exists('elgg_get_file_simple_type'),
			'elgg_get_file_simple_type was removed in Elgg 6.x — the upload flow fatals on 7.x'
		);

		$mimetype = _elgg_services()->mimetype;
		$this->assertInstanceOf(\Elgg\Filesystem\MimeTypeService::class, $mimetype);

		// The classification Upload::save() depends on: 'image' selects the Image resize branch.
		$this->assertSame('image', $mimetype->getSimpleType('image/png'));
		$this->assertSame('image', $mimetype->getSimpleType('image/jpeg'));
		$this->assertSame('document', $mimetype->getSimpleType('application/pdf'));
		$this->assertSame('general', $mimetype->getSimpleType('application/octet-stream'));

		// Post-migration: parseSimpleType() now classifies via the mimetype service.
		$upload = $this->source('classes/hypeJunction/Filestore/Handlers/Uploader/Upload.php');
		$this->assertDoesNotMatchRegularExpression(
			'/(?<![\w>$:\\\\])elgg_get_file_simple_type\s*\(/',
			$upload,
			'Upload::parseSimpleType() must no longer call removed elgg_get_file_simple_type'
		);
		$this->assertMatchesRegularExpression(
			'/_elgg_services\(\)->mimetype->getSimpleType\s*\(/',
			$upload,
			'Upload::parseSimpleType() must classify via MimeTypeService::getSimpleType'
		);
	}
}
