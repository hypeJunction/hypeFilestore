<?php

namespace hypeJunction\Filestore;

use ElggFile;
use stdClass;

/**
 * Handles file uploads
 *
 * @package    HypeJunction
 * @subpackage Filestore
 */
class UploadHandler {

	
	/**
	 * Stores file entities that were created from uploaded files
	 * @var array
	 */
	private static $files;

	/**
	 * Additional attributes to be used when creating file entities from uploaded files
	 * Can specify such information as subtype, owner_guid, container_guid as well as any other metadata to attach
	 * @var array
	 */
	static $attributes;

	/**
	 * Config information to be used
	 * @uses $config['icon_sizes']  Icon sizes to create after upload
	 * @uses $config['filestore_prefix'] Prefix on the filestore. Default is 'file/'
	 * @uses $config['icon_filestore_prefix'] Icon prefix on the filestore. Default is 'icons/'. Entity guid is appended automatically
	 * @var array
	 */
	static $config;

	/**
	 * Constructor
	 */
	protected function __construct() {}

	public function setInputName($input) {
		$this->input = $input;
	}
	
	public function setEntityAttributes(array $attributes = array()) {
		$this->attributes = $attributes;
	}
	
	/**
	 * Static counterpart of makeFiles, but returns data for processed uploads
	 *
	 * @param string $input      Name of the file input
	 * @param array  $attributes Key value pairs, such as subtype, owner_guid, metadata.
	 * @param array  $config     Additional config
	 * @return array An array of file entities created
	 */
	public static function handle($input, array $attributes = array(), array $config = array()) {

		$handler = new UploadHandler;
		$handler->makeFiles($input, $attributes, $config);

		return self::$uploads[$input];
	}

	/**
	 * Create new file entities
	 *
	 * @param string $input      Name of the file input
	 * @param array  $attributes Key value pairs, such as subtype, owner_guid, metadata.
	 * @param array  $config     Additional config
	 * @return array An array of file entities created
	 */
	public function makeFiles($input, array $attributes = array(), array $config = array()) {
		if (!isset(self::$files)) {
			self::$files = array();
		}
		if (!array_key_exists($input, self::$files)) {
			self::$attributes = $attributes;
			self::$config = $config;
			self::$files[$input] = self::entityFactory($input);
		}

		return self::$files[$input];
	}

	/**
	 * Create new entities from file uploads
	 *
	 * @param string $input Name of the file input
	 * @return void
	 */
	protected static function entityFactory($input) {

		if (!isset(self::$config['filestore_prefix'])) {
			$prefix = "file/";
		}

		$uploads = self::$uploads[$input];
		$handled_uploads = array();
		$entities = array();

		foreach ($uploads as $key => $upload) {
			if ($upload->error) {
				$handled_uploads[] = $upload;
				continue;
			}


			$filehandler = new ElggFile();
			if (is_array(self::$attributes)) {
				foreach (self::$attributes as $key => $value) {
					$filehandler->$key = $value;
				}
			}

			$filestorename = elgg_strtolower(time() . $upload->name);
			$filehandler->setFilename($prefix . $filestorename);

			$filehandler->title = $upload->name;
			$filehandler->originalfilename = $upload->name;
			$filehandler->filesize = $upload->size;
			$filehandler->mimetype = $upload->mimetype;
			$filehandler->simpletype = $upload->simpletype;

			$filehandler->open("write");
			$filehandler->close();

			move_uploaded_file($upload->path, $filehandler->getFilenameOnFilestore());

			if ($filehandler->save()) {
				$upload->guid = $filehandler->getGUID();
				$upload->file = $filehandler;

				if ($filehandler->simpletype == "image") {
					IconHandler::makeIcons($filehandler);
				}

				$entities[] = $filehandler;
			} else {
				$upload->error = elgg_echo('upload:error:unknown');
			}

			$handled_uploads[] = $upload;
		}

		self::$uploads[$input] = $handled_uploads;
		self::$files[$input] = $entities;
	}

	/**
	 * Get readable error status from $_FILES global
	 *
	 * @param int $code Error code
	 * @return boolean
	 */
	protected static function getError($code) {

		switch ($code) {
			case UPLOAD_ERR_OK:
				return false;
			case UPLOAD_ERR_NO_FILE:
				return elgg_echo('upload:error:no_file');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return elgg_echo('upload:error:file_size');
			default:
				return elgg_echo('upload:error:unknown');
		}
	}

	/**
	 * Detect mimetype of the uploaded file
	 *
	 * @param array $file Uploaded file
	 * @return string
	 */
	protected static function detectMimeType($file) {
		$mimetype = ElggFile::detectMimeType($file['tmp_name'], $file['type']);

		// Hack for Microsoft zipped formats
		$info = pathinfo($file['name']);
		$office_formats = array('docx', 'xlsx', 'pptx');
		if ($mimetype == "application/zip" && in_array($info['extension'], $office_formats)) {
			switch ($info['extension']) {
				case 'docx':
					$mimetype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
					break;
				case 'xlsx':
					$mimetype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
					break;
				case 'pptx':
					$mimetype = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
					break;
			}
		}

		// Check for bad ppt detection
		if ($mimetype == "application/vnd.ms-office" && $info['extension'] == "ppt") {
			$mimetype = "application/vnd.ms-powerpoint";
		}

		return $mimetype;
	}

	/**
	 * Get a simple type such as 'image'
	 *
	 * @param string $mimetype Mimetype
	 * @return string
	 */
	protected static function parseSimpleType($mimetype) {

		switch ($mimetype) {
			case "application/msword":
			case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
				return "document";
			case "application/pdf":
				return "document";
			case "application/ogg":
				return "audio";
		}

		if (substr_count($mimetype, 'text/')) {
			return "document";
		}

		if (substr_count($mimetype, 'audio/')) {
			return "audio";
		}

		if (substr_count($mimetype, 'image/')) {
			return "image";
		}

		if (substr_count($mimetype, 'video/')) {
			return "video";
		}

		if (substr_count($mimetype, 'opendocument')) {
			return "document";
		}

		return "general";
	}

	/**
	 * Get human-readable size
	 * 
	 * @param int $decimals Decimals to include in the size string
	 * @return string
	 */
	public function getHumanReadableSize($decimals = 2) {
		$bytes = (int) $this->getSize();
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $sz[$factor];
	}

}
