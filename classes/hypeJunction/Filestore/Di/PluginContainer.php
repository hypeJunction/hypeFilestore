<?php

namespace hypeJunction\Filestore\Di;

use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Handlers\Uploader;
use hypeJunction\Filestore\Icons\Factory;
use hypeJunction\Filestore\Listeners\PluginHooks;

/**
 * Filestore service provider.
 *
 * Lazy-loaded service container. Replaces the old `extends \Elgg\Di\DiContainer`
 * + setFactory() pattern (Elgg 2.x/3.x API removed in 4.x — DiContainer became
 * abstract and no longer exposes setFactory; PHP-DI 6 took over inside Elgg).
 *
 * Outward interface preserved: callers still do
 * `hypeFilestore()->config`, `->hooks`, `->uploader`, `->iconFactory`.
 *
 * @property-read Config      $config
 * @property-read PluginHooks $hooks
 * @property-read Uploader    $uploader
 * @property-read Factory     $iconFactory
 */
class PluginContainer
{
	/** @var Config|null */
	private $config;

	/** @var PluginHooks|null */
	private $hooks;

	/** @var Uploader|null */
	private $uploader;

	/** @var Factory|null */
	private $iconFactory;

	/**
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
	{
		switch ($key) {
			case 'config':
				return $this->config ?? ($this->config = Config::factory());
			case 'iconFactory':
				return $this->iconFactory ?? ($this->iconFactory = new Factory($this->__get('config')));
			case 'hooks':
				return $this->hooks ?? ($this->hooks = new PluginHooks($this->__get('config'), $this->__get('iconFactory')));
			case 'uploader':
				return $this->uploader ?? ($this->uploader = new Uploader($this->__get('config'), $this->__get('iconFactory')));
		}
		throw new \RuntimeException("Undefined service: $key");
	}

	/**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
	{
		return in_array($key, ['config', 'hooks', 'uploader', 'iconFactory'], true);
	}

	/**
	 * Creates a new ServiceProvider instance.
	 *
	 * @return PluginContainer
	 */
	public static function create(): self
	{
		return new self();
	}
}
