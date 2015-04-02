<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

/**
 * Plugin DI Container
 * @staticvar \hypeJunction\Filestore\Di\PluginContainer $provider
 * @return \hypeJunction\Filestore\Di\PluginContainer
 */
function hypeFilestore() {
	static $provider;
	if (null === $provider) {
		$provider = \hypeJunction\Filestore\Di\PluginContainer::create();
	}
	return $provider;
}
