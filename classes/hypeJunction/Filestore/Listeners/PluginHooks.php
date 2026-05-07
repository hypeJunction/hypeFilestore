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
		// Event registration is in elgg-plugin.php 'events' key. No-op here for call-surface compat.
	}

	/**
	 * Filter icon URLs to route requests via a faster handler.
	 *
	 * @param \Elgg\Event $event Event with the entity whose icon URL is requested
	 * @return string|null
	 */
	public static function handleEntityIconUrls(\Elgg\Event $event) {
		$existing = $event->getValue();
		if (!is_null($existing)) {
			// another plugin has already replaced the icon URL
			return $existing;
		}

		$entity = $event->getParam('entity');
		if (!$entity) {
			return $existing;
		}

		$size = $event->getParam('size', 'medium');

		$factory = hypeFilestore()->iconFactory;
		if (!$entity->icontime || !array_key_exists($size, $factory->getSizes($entity))) {
			// icon has not yet been created or the icon size is unknown
			return $existing;
		}

		return $factory->getURL($entity, $size);
	}
}
