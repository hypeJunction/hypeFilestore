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
	 * Perform tasks on system init.
	 *
	 * Retained for backwards compatibility with any caller that still invokes
	 * hypeFilestore()->hooks->init() directly (e.g., tests or downstream
	 * plugins). The entity:icon:url hook itself is now declared in
	 * elgg-plugin.php and does NOT need to be registered here.
	 *
	 * @return void
	 */
	public function init() {
		// Hook registration moved to elgg-plugin.php 'hooks' key (Elgg 4.x
		// declarative config). This method intentionally no-ops to preserve
		// the call surface.
	}

	/**
	 * Filter icon URLs to route requests via a faster handler.
	 *
	 * Elgg 4.x \Elgg\Hook signature — replaces the legacy 4-arg
	 * ($hook, $type, $return, $params) form.
	 *
	 * @param \Elgg\Hook $hook
	 * @return string|null
	 */
	public static function handleEntityIconUrls(\Elgg\Hook $hook) {
		$existing = $hook->getValue();
		if (!is_null($existing)) {
			// another plugin has already replaced the icon URL
			return $existing;
		}

		$entity = $hook->getEntityParam();
		if (!$entity) {
			return $existing;
		}
		$size = $hook->getParam('size', 'medium');

		$factory = hypeFilestore()->iconFactory;
		if (!$entity->icontime || !array_key_exists($size, $factory->getSizes($entity))) {
			// icon has not yet been created or the icon size is unknown
			return $existing;
		}

		return $factory->getURL($entity, $size);
	}

}
