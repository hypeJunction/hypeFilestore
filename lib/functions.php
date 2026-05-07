<?php

/**
 * Plugin helper functions for hypeFilestore.
 *
 * Loaded once via require_once at the top of elgg-plugin.php (Iron Law 5:
 * elgg-plugin.php cannot contain closures, so the hypeFilestore() factory
 * lives here instead of in elgg-plugin.php).
 */

if (!function_exists('hypeFilestore')) {

	/**
	 * Plugin DI Container singleton.
	 *
	 * @return \hypeJunction\Filestore\Di\PluginContainer
	 */
	function hypeFilestore() {
		static $provider;
		if ($provider === null) {
			$provider = \hypeJunction\Filestore\Di\PluginContainer::create();
		}

		return $provider;
	}
}
