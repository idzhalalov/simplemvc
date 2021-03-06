<?php
define('APP_URL', 'http://' . str_replace('/', '', $_SERVER['SERVER_NAME']));

$config = [
    'database' => [
        'host' => '',
        'db_name' => '',
        'user' => '',
        'password' => '',
    ],
    'application' => [
        'url' => APP_URL,
        'absolute_path' => __DIR__,
        'controllers' => __DIR__ . '/lib/controllers',
        'models' => __DIR__ . '/lib/models',
        'views' => __DIR__ . '/lib/views',
        'views_cache' => __DIR__ . '/lib/views/cache',
    ],
    'pictures' => [
        'max_width' => 320,
        'max_height' => 240,
        'path' => __DIR__ . '/pictures',
    ],
    'logger' => [
        'default' => [
            [
                'type' => 'stream',
                // the level of log messages to include (optional)
                'level' => \Psr\Log\LogLevel::ERROR,
                // logger specific parameters
                'path' => __DIR__ . '/logs/app.log'
            ]
        ]
    ]
];
