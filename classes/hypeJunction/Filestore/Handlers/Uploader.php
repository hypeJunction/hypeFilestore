<?php

namespace hypeJunction\Filestore\Handlers;

use ElggEntity;
use ElggFile;
use hypeJunction\Filestore\Config\Config;
use hypeJunction\Filestore\Handlers\Uploader\Upload;
use hypeJunction\Filestore\Icons\Factory;


class Uploader {

	/**
	 * Config
	 * @var Config
	 */
	private $config;

	/**
	 * Icons factory
	 * @var Factory
	 */
	private $iconFactory;

	/**
	 * Constructor
	 *
	 * @param Config $config      Config
	 * @param Factory $iconFactory Icon Factory
	 */
	public function __construct(Config $config, Factory $iconFactory) {
		$this->config = $config;
		$this->iconFactory = $iconFactory;
	}

	/**
	 * Create new ElggFile entities from uploaded files
	 *
	 * @param string $input      Name of the file input
	 * @param array  $attributes Attributes and metadata for saving files
	 * @param array  $options    Additional factory options (including entity attributes such as type, subtype, owner_guid, container_guid, access_id etc
	 *                             'icon_sizes'            ARR Optional. An array of icon sizes to create (for image uploads)
	 *                             'coords'                ARR Optional. Coordinates for icon cropping
	 *                             'filestore_prefix'      STR Optional. Custom prefix on Elgg filestore
	 *                             'icon_filestore_prefix' STR Optional. Custom prefix for created icons on Elgg filestore
	 * @return ElggFile[]
	 */
	public function handle($input = '', array $attributes = array(), array $options = array()) {

		$result = array();
		$uploads = $this->getUploads($input);

		$filestore_prefix = elgg_extract('filestore_prefix', $options, $this->config->getDefaultFilestorePrefix());
		unset($options['filestore_prefix']);
		
		foreach ($uploads as $props) {

			$upload = new Upload($props);
			$upload->save($attributes, $filestore_prefix);

			if ($upload->file instanceof ElggEntity && $upload->simpletype == 'image') {
				$this->iconFactory->create($upload->file, null, $options);
			}

			$result[] = $upload;
		}

		return $result;
	}

	/**
	 * Returns the $_FILES global
	 * @return array
	 */
	protected function getGlobals() {
		return (is_array($_FILES)) ? $_FILES : array();
	}

	/**
	 * Extracts upload information from the $_FILES global
	 *
	 * @todo: support for multidimensional inputs?
	 *
	 * @param string $input Form field/input name
	 * @return array
	 */
	protected function getUploads($input = '') {

		$global = $this->getGlobals();
		$uploads = array();
		$input = (string) $input;

		if (empty($global) || !isset($global[$input])) {
			// input name was not present in the form
			return $uploads;
		}

		$keys = array_keys($global[$input]);
		$primary_key = $keys[0];

		if (is_array($global[$input][$primary_key])) {
			// multiple file input
			// in case inputs are named (e.g. name="upload[file1]")
			$input_keys = array_keys($global[$input][$primary_key]);

			foreach ($input_keys as $i) {
				$upload = array();
				foreach ($keys as $key) {
					$upload[$key] = $global[$input][$key][$i];
				}
				$uploads[] = $upload;
			}
		} else {
			// regular file input
			$uploads[] = $global[$input];
		}

		return $uploads;
	}

}
