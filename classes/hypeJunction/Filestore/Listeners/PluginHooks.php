<?php

namespace hypeJunction\Filestore\Listeners;

use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Icons\Factory;


/**
 * Plugin hooks service
 */
class PluginHooks {

	/**
	 *
	 * @var Config
	 */
	private $config;

	/**
	 *
	 * @var Factory
	 */
	private $iconFactory;

	/**
	 * Constructor
	 *
	 * @param Config  $config  Config
	 * @param Factory $factory Icon factory
	 */
	public function __construct(Config $config, Factory $factory) {
		$this->config = $config;
		$this->iconFactory = $factory;
	}

	/**
	 * Perform tasks on system init
	 * @return void
	 */
	public function init() {
		elgg_register_plugin_hook_handler('entity:icon:url', 'all', array($this, 'handleEntityIconUrls'));
	}

	/**
	 * Filter icon URLs to route requests via a faster handler
	 *
	 * @param string $hook   "entity:icon:url"
	 * @param string $type   "all"
	 * @param string $return URL
	 * @param array  $params Hook params
	 * @return string
	 */
	function handleEntityIconUrls($hook, $type, $return, $params) {

		if (!is_null($return)) {
			// another plugin has already replaced the icon URL
			return $return;
		}

		$entity = elgg_extract('entity', $params);
		$size = elgg_extract('size', $params, 'medium');

		if (!$entity->icontime || !array_key_exists($size, $this->iconFactory->getSizes($entity))) {
			// icon has not yet been created or the icon size is unknown
			return $return;
		}

		return $this->iconFactory->getURL($entity, $size);
	}

}
