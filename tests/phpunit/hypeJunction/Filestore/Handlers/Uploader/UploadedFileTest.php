<?php

namespace hypeJunction\Filestore;

use PHPUnit_Framework_TestCase;
/**
 * @coversDefaultClass hypeJunction\Filestore\UploadedFile
 */
class UploadedFileTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var UploadedFile
	 */
	protected $object;
	/**
	 * @var array
	 */
	protected $params;
	protected function setUp() {

		$this->params = array(
			'name' => 'MyFile.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => '/tmp/php/php6hst32',
			'error' => UPLOAD_ERR_OK,
			'size' => 98174,
			'foo' => 'bar',
		);

		$this->object = UploadedFile::factory($this->params);
	}
	protected function tearDown() {

	}
	/**
	 * @covers ::__construct
	 * @covers ::factory
	 */
	public function testFactory() {
		$this->assertNotNull($this->object);
	}
	/**
	 * @covers ::getData
	 */
	public function testGetData() {
		$this->assertEquals('bar', $this->object->getData('foo'));
		$this->assertNull($this->object->getData('bar'));
	}
	/**
	 * @covers ::getFilename
	 */
	public function testGetFilename() {
		$this->assertEquals('MyFile.jpg', $this->object->getFilename());
	}
	/**
	 * @covers ::getLocation
	 */
	public function testGetLocation() {
		$this->assertEquals('/tmp/php/php6hst32', $this->object->getLocation());
	}
	/**
	 * @covers ::getMimetype
	 */
	public function testGetMimetype() {
		$this->assertEquals('image/jpeg', $this->object->getMimetype());
	}

	/**
	 * @covers ::getSize
	 */
	public function testGetSize() {
		$this->assertEquals(98174, $this->object->getSize());
	}

	/**
	 * @covers ::getError
	 * @covers ::isSuccessful
	 * @dataProvider providerErrors
	 */
	public function testGetError($error_code, $is_successful, $is_error) {
		$this->params['error'] = $error_code;
		$this->object = UploadedFile::factory($this->params);
		$this->assertEquals($is_successful, $this->object->isSuccessful());
		$this->assertEquals($is_error, (bool) $this->object->getError());
	}
	public function providerErrors() {
		return array(
			array(UPLOAD_ERR_OK, true, false),
			array(UPLOAD_ERR_NO_FILE, false, true),
			array(UPLOAD_ERR_INI_SIZE, false, true),
			array(UPLOAD_ERR_FORM_SIZE, false, true),
			array(100, false, true),
		);
	}
}