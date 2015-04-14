<?php

$base_dir = dirname(dirname(dirname(dirname(__FILE__))));

require_once $base_dir . '/engine/settings.php';
require_once $base_dir . '/engine/lib/autoloader.php';

require_once dirname(dirname(__FILE__)) . '/lib/autoloader.php';

global $CONFIG;
$conf = new \Elgg\Database\Config($CONFIG);

$server = new \hypeJunction\Filestore\Icons\Server($conf, $CONFIG->dbprefix);
$server->serve();