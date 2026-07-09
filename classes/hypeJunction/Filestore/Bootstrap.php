<?php

namespace hypeJunction\Filestore;

use Elgg\DefaultPluginBootstrap;

/**
 * hypeFilestore plugin bootstrap.
 *
 * The plugin is service-only — no public routes, entities, or UI views.
 * Bootstrap::init() registers the cropper assets that consumer plugins
 * (hypeAttachments, hypeWall) load into image-editing forms.
 *
 * The entity:icon:url hook is registered declaratively in elgg-plugin.php
 * (see 'hooks' key) — it does not need to be re-registered here.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function load(): void {
		// No-op. PSR-4 autoload handled by composer.
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot(): void {
		// No-op. The plugin's services are constructed lazily via the
		// hypeFilestore() factory in lib/functions.php.
	}

	/**
	 * {@inheritdoc}
	 */
	public function init(): void {
		// No-op. The elgg_register_css and elgg_register_external_view helpers
		// were removed in Elgg 4.x — assets at views/default/css/ and
		// views/default/js/ auto-discover via simplecache. Consumer plugins
		// (hypeAttachments, hypeWall) that need cropper assets reference
		// them directly via their own view files.
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready(): void {
		// No-op.
	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown(): void {
		// No-op.
	}

	/**
	 * {@inheritdoc}
	 */
	public function activate(): void {
		// No-op. No schema or settings to provision.
	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate(): void {
		// No-op.
	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade(): void {
		// No-op.
	}
}
