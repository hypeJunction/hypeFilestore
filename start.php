<?php

/**
 * File and image handling utilities
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 * @copyright Copyright (c) 2011-2015, Ismayil Khayredinov
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */

require_once __DIR__ . '/vendor/autoload.php';

elgg_register_event_handler('init', 'system', function() {

	hypeFilestore()->hooks->init();

	/**
	 * JS/CSS
	 */
	elgg_register_css('cropper', '/mod/hypeFilestore/vendors/cropper/dist/cropper.min.css');
	elgg_define_js('cropper', array(
		'src' => '/mod/hypeFilestore/vendors/cropper/dist/cropper.min.js',
		'deps' => array('jquery')
	));
});

/**
 * Plugin DI Container
 * @staticvar \hypeJunction\Filestore\Di\PluginContainer $provider
 * @return \hypeJunction\Filestore\Di\PluginContainer
 */
function hypeFilestore() {
	static $provider;
	if (null === $provider) {
		$provider = \hypeJunction\Filestore\Di\PluginContainer::create();
	}
	return $provider;
}
