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
class Bootstrap extends DefaultPluginBootstrap
{
    public function load(): void
    {
        // No-op. PSR-4 autoload handled by composer.
    }

    public function boot(): void
    {
        // No-op. The plugin's services are constructed lazily via the
        // hypeFilestore() factory in lib/functions.php.
    }

    public function init(): void
    {
        // Register cropper assets for consumer plugins.
        \elgg_register_css('cropper', '/mod/hypefilestore/vendors/cropper/dist/cropper.min.css');
        \elgg_register_external_view('js/cropper');
    }

    public function ready(): void
    {
        // No-op.
    }

    public function shutdown(): void
    {
        // No-op.
    }

    public function activate(): void
    {
        // No-op. No schema or settings to provision.
    }

    public function deactivate(): void
    {
        // No-op.
    }

    public function upgrade(): void
    {
        // No-op.
    }
}
