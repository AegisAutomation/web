<?php
/**
 * Configuration File
 */

return [
    'database' => [
        'host' => 'localhost',
        'name' => 'my_database',
        'user' => 'db_user',
        'pass' => 'db_password',
        'port' => 3306,
        'charset' => 'utf8mb4',
    ],

    'ssh' => [
        'enabled' => false,
        'key_path' => '/home/username/.ssh/id_rsa',
    ],

    'app' => [
        'debug' => true,
        'base_url' => 'http://localhost/project',
        'timezone' => 'UTC',
    ],
];
