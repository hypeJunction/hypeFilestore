<?php

namespace hypeJunction\Filestore\Di;

use Elgg\Di\DiContainer;
use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Handlers\Uploader;
use hypeJunction\Filestore\Icons\Factory;
use hypeJunction\Filestore\Listeners\PluginHooks;

/**
 * Filestore service provider
 *
 * @property-read Config      $config
 * @property-read PluginHooks $hooks
 * @property-read Uploader    $uploader
 * @property-read Factory     $iconFactory
 */
class PluginContainer extends DiContainer {

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->setFactory('config', '\hypeJunction\Filestore\Config\Config::factory');

		$this->setFactory('hooks', function (PluginContainer $c) {
			return new PluginHooks($c->config, $c->iconFactory);
		});

		$this->setFactory('uploader', function(PluginContainer $c) {
			return new Uploader($c->config, $c->iconFactory);
		});

		$this->setFactory('iconFactory', function(PluginContainer $c) {
			return new Factory($c->config);
		});

	}

	/**
	 * Creates a new  ServiceProvider instance
	 * @return PluginContainer
	 */
	public static function create() {
		return new PluginContainer();
	}

}
