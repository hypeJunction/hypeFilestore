<?php

namespace hypeJunction\Filestore;

class UploadedFile {

	/**
	 * Filename of the uploaded file
	 * @var string 
	 */
	protected $name;

	/**
	 * Mimetype of the uploaded file
	 * @var string 
	 */
	protected $type;

	/**
	 * Temp location of the uploaded file
	 * @var type 
	 */
	protected $tmp_name;

	/**
	 * Upload error/success status
	 * @var type 
	 */
	protected $error;

	/**
	 * Size of the uploaded file
	 * @var type 
	 */
	protected $size;

	/**
	 * Extraneous parameters
	 * @var array 
	 */
	protected $data;

	/**
	 * Constructor
	 */
	protected function __construct() {}

	/**
	 * Construct a new UploadedFile from an array of params
	 * 
	 * @param array $params
	 * @return UploadedFile
	 */
	public static function factory(array $params = array()) {
		$file = new UploadedFile();
		$properties = get_class_vars(get_class($file));
		foreach ($params as $key => $value) {
			if (array_key_exists($key, $properties)) {
				$file->$key = $value;
			} else {
				$file->data[$key] = $value;
			}
		}
		return $file;
	}

	/**
	 * Get an extraneous parameter
	 * 
	 * @param string $name Parameter name
	 * @return mixed
	 */
	public function getData($name = '') {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		return null;
	}

	/**
	 * Get filename
	 * @return string
	 */
	public function getFilename() {
		return (string) $this->name;
	}

	/**
	 * Get file location
	 * @return string
	 */
	public function getLocation() {
		return (string) $this->tmp_name;
	}

	/**
	 * Get file mimetype
	 * @return string
	 */
	public function getMimetype() {
		return (string) $this->type;
	}

	/**
	 * Check if upload was successful
	 * @return boolean
	 */
	public function isSuccessful() {
		return !($this->getError());
	}

	/**
	 * Get human readable upload error
	 * @return string|boolean
	 */
	public function getError() {
		switch ($this->error) {
			case UPLOAD_ERR_OK:
				return false;
			case UPLOAD_ERR_NO_FILE:
				$error = 'upload:error:no_file';
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$error = 'upload:error:file_size';
			default:
				$error = 'upload:error:unknown';
		}

		return elgg_echo($error);
	}

	/**
	 * Get file size in bytes
	 * @return int
	 */
	public function getSize() {
		return (int) $this->size;
	}

}
