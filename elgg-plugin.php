<?php

require_once __DIR__ . '/lib/functions.php';

return [
    'plugin' => [
        'name' => 'hypeFilestore',
        'version' => '5.0.0',
        'activate_on_install' => false,
    ],
    'bootstrap' => \hypeJunction\Filestore\Bootstrap::class,
    'events' => [
        'entity:icon:url' => [
            'all' => [
                \hypeJunction\Filestore\Listeners\PluginHooks::class . '::handleEntityIconUrls' => [],
            ],
        ],
    ],
];
