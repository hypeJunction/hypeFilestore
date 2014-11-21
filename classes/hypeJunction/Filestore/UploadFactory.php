<?php

namespace hypeJunction\Filestore;

class UploadFactory {

	/**
	 * Normalized array of uploaded files
	 * @var array 
	 */
	protected $uploadedFiles;

	/**
	 * Constructor
	 */
	protected function __construct() {
		
	}

	/**
	 * Convert file uploads into an object and get any errors
	 *
	 * @param array $_files Normalized $_FILES global
	 * @return array
	 */
	public static function factory($_files = array()) {

		$factory = new UploadFactory();
	}

	public function prepareFiles() {
		$_files = self::normalize($_files);

		foreach ($_files as $input => $uploads) {
			foreach ($uploads as $upload) {
				$object = new stdClass();
				if (is_array($upload)) {
					foreach ($upload as $key => $value) {
						$object->$key = $value;
					}

					$object->error = self::getError($object->error);
					if (!$object->error) {
						$object->filesize = $upload['size'];
						$object->mimetype = self::detectMimeType($upload);
						$object->simpletype = self::parseSimpletype($object->mimetype);
						$object->path = $upload['tmp_name'];
					}
				}
				self::$uploads[$input][] = $object;
			}
		}

		return self::$uploads;
	}

	/**
	 * Nomalizes $_FILES global
	 *
	 * @param array   $_files An array of uploaded files
	 * @param boolean $top    Is this the top level array
	 * @return array
	 */
	protected static function normalize(array $_files = array(), $top = true) {

		$files = array();
		foreach ($_files as $name => $file) {
			if ($top) {
				$sub_name = $file['name'];
			} else {
				$sub_name = $name;
			}
			if (is_array($sub_name)) {
				foreach (array_keys($sub_name) as $key) {
					$files[$name][$key] = array(
						'name' => $file['name'][$key],
						'type' => $file['type'][$key],
						'tmp_name' => $file['tmp_name'][$key],
						'error' => $file['error'][$key],
						'size' => $file['size'][$key],
					);
					$files[$name] = self::normalize($files[$name], FALSE);
				}
			} else {
				$files[$name] = $file;
			}
		}

		return $files;
	}

}
