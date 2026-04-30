<?php

require_once __DIR__ . '/lib/functions.php';

return [
    'plugin' => [
        'name' => 'hypeFilestore',
        'version' => '4.0.0',
        'activate_on_install' => false,
    ],
    'bootstrap' => \hypeJunction\Filestore\Bootstrap::class,
    'hooks' => [
        'entity:icon:url' => [
            'all' => [
                \hypeJunction\Filestore\Listeners\PluginHooks::class . '::handleEntityIconUrls' => [],
            ],
        ],
    ],
];
