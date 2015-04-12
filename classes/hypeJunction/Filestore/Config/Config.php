<?php

namespace hypeJunction\Filestore\Config;

use ElggPlugin;

/**
 * Config
 */
class Config {

	const PLUGIN_ID = 'hypeFilestore';
	const SIZE_TOPBAR = 'topbar';
	const SIZE_TINY = 'tiny';
	const SIZE_SMALL = 'small';
	const SIZE_MEDIUM = 'medium';
	const SIZE_LARGE = 'large';
	const SIZE_MASTER = 'master';

	private $plugin;
	private $settings;
	private $config = array(
		'filestore_prefix' => 'file',
		'icon_filestore_prefix' => 'icons',
		'default_size' => self::SIZE_MEDIUM,
		'master_size_length' => 550,
	);

	/**
	 * Constructor
	 * @param ElggPlugin $plugin ElggPlugin
	 */
	public function __construct(ElggPlugin $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * Config factory
	 * @return Config
	 */
	public static function factory() {
		$plugin = elgg_get_plugin_from_id(self::PLUGIN_ID);
		return new Config($plugin);
	}

	/**
	 * Returns all plugin settings
	 * @return array
	 */
	public function all() {
		if (!isset($this->settings)) {
			$plugin_settings = array_filter($this->plugin->getAllSettings());
			$this->settings = array_merge($this->config, $plugin_settings);
		}
		return $this->settings;
	}

	/**
	 * Returns a plugin setting
	 *
	 * @param string $name Setting name
	 * @return mixed
	 */
	public function get($name, $default = null) {
		return elgg_extract($name, $this->all(), $default);
	}

	/**
	 * Returns default filestore prefix for saving uploads
	 * @return string
	 */
	public function getDefaultFilestorePrefix() {
		return $this->get('filestore_prefix', 'file');
	}

	/**
	 * Returns default filestore prefix for saving icons
	 * @return string
	 */
	public function getDefaultIconDirectory() {
		return $this->get('icon_filestore_prefix', 'icons');
	}

	/**
	 * Returns plugin path
	 * @return string
	 */
	public function getPath() {
		return $this->plugin->getPath();
	}

	/**
	 * Returns an array of croppable size names
	 * @return array
	 */
	public function getCroppableSizes() {
		return array(
			self::SIZE_LARGE,
			self::SIZE_MEDIUM,
			self::SIZE_SMALL,
			self::SIZE_TINY,
			self::SIZE_TOPBAR,
		);
	}

	/**
	 * Returns default sizes for file object icons
	 * @return array
	 */
	public function getFileIconSizes() {
		return array(
			'small' => array(
				'w' => 60,
				'h' => 60,
				'square' => true,
				'upscale' => true,
				'metadata_name' => 'thumbnail',
			),
			'medium' => array(
				'w' => 153,
				'h' => 153,
				'square' => true,
				'upscale' => true,
				'metadata_name' => 'smallthumb',
			),
			'large' => array(
				'w' => 600,
				'h' => 600,
				'square' => true,
				'upscacle' => true,
				'metadata_name' => 'largethumb',
			)
		);
	}

	/**
	 * Returns global icon sizes
	 * @return array
	 */
	public function getGlobalIconSizes() {
		return elgg_get_config('icon_sizes');
	}

	/**
	 * Returns additional quality options for icon compression
	 * @return array
	 */
	public function getIconCompressionOpts() {
		return array(
			'jpeg_quality' => $this->get('icon_jpeg_quality', 100),
			'png_compression' => $this->get('icon_png_compression', 0),
			'png_filter' => $this->get('icon_png_filter', PNG_NO_FILTER),
		);
	}

	/**
	 * Returns additional quality options for source file compression
	 * @return array
	 */
	public function getSrcCompressionOpts() {
		return array(
			'jpeg_quality' => $this->get('jpeg_quality', 100),
			'png_compression' => $this->get('png_compression', 0),
			'png_filter' => $this->get('png_filter', PNG_NO_FILTER),
		);
	}

}
