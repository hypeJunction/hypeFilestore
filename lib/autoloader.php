<?php

elgg_register_classes(dirname(dirname(__FILE__)) . '/classes/');
elgg_register_classes(dirname(dirname(__FILE__)) . '/vendors/WideImage/lib/');

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
