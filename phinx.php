<?php

require 'secure/vars.php';
/**
 * @var string $host
 * @var string $database
 * @var string $username
 * @var string $password
 * @var string $ENVIRONMENT "prod"|"dev"
 */

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $ENVIRONMENT,
        'prod' => [
            'adapter' => 'mysql',
            'host' => $host,
            'name' => $database,
            'user' => $username,
            'pass' => $password,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'dev' => [
            'adapter' => 'mysql',
            'host' => $host,
            'name' => $database,
            'user' => $username,
            'pass' => $password,
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
