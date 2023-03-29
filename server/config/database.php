<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => 'ms_',
            'prefix_indexes' => true,
            'strict'         => false,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'schema'         => 'public',
            'sslmode'        => 'prefer',
        ],

        'sqlsrv' => [
            'driver'         => 'sqlsrv',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', 'localhost'),
            'port'           => env('DB_PORT', '1433'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        /*'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],*/

        'default'           => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => env('REDIS_DB', '0'),
            'read_write_timeout' => 1
        ],
        //session弃用
        'session'           => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 2,
            'read_write_timeout' => 1
        ],
        'cache'             => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => env('REDIS_CACHE_DB', '1'),
            'read_write_timeout' => 1
        ],
        'token'             => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 2,
            'read_write_timeout' => 1
        ],

        // 触发规则加入到黑名单
        'rule_black'        => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 5,
            'read_write_timeout' => 1
        ],
        // 规则设置的黑名单时间
        'black_time'        => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 6,
            'read_write_timeout' => 1
        ],
        // 规则类型
        'rules-type'        => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 7,
            'read_write_timeout' => 1
        ],
        'host'              => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 8,
            'read_write_timeout' => 1
        ],
        'logstash'          => [
            'host'               => env('OWNER_REDIS_HOST', '127.0.0.1'),
            'password'           => env('OWNER_REDIS_PASSWORD', null),
            'port'               => env('OWNER_REDIS_PORT', 6379),
            'database'           => 9,
            'read_write_timeout' => 1
        ],
        'queue'             => [
            'host'               => env('OWNER_REDIS_HOST', '127.0.0.1'),
            'password'           => env('OWNER_REDIS_PASSWORD', null),
            'port'               => env('OWNER_REDIS_PORT', 6379),
            'database'           => 10,
            'read_write_timeout' => 1
        ],
        'logstash_fish'     => [
            'host'               => env('REDIS_HOST', '127.0.0.1'),
            'password'           => env('REDIS_PASSWORD', null),
            'port'               => env('REDIS_PORT', 6379),
            'database'           => 11,
            'read_write_timeout' => 1
        ],
        'horizon'           => [
            'host'               => env('OWNER_REDIS_HOST', '127.0.0.1'),
            'password'           => env('OWNER_REDIS_PASSWORD', null),
            'port'               => env('OWNER_REDIS_PORT', 6379),
            'database'           => 12,
            'read_write_timeout' => 1
        ],
        'iptables'          => [
            'host'               => env('OWNER_REDIS_HOST', '127.0.0.1'),
            'password'           => env('OWNER_REDIS_PASSWORD', null),
            'port'               => env('OWNER_REDIS_PORT', 6379),
            'database'           => 13,
            'read_write_timeout' => 1
        ],
        'iptables-clusters' => [
            'master'  => [
                'host'     => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port'     => env('REDIS_PORT', 6379),
                'database' => 13,
            ],
            // 'slave-1' => [
            //     'host'     => env('REDIS_HOST_SLAVE_1'),
            //     'password' => env('REDIS_PASSWORD_SLAVE_1', null),
            //     'port'     => env('REDIS_PORT_SLAVE_1', 6379),
            //     'database' => 13,
            // ],
            // 'slave-2' => [
            //     'host'     => env('REDIS_HOST_SLAVE_2'),
            //     'password' => env('REDIS_PASSWORD_SLAVE_2', null),
            //     'port'     => env('REDIS_PORT_SLAVE_2', 6379),
            //     'database' => 13,
            // ],
        ],

        'syncmd'          => [
            'host'     => env('OWNER_REDIS_HOST', '127.0.0.1'),
            'password' => env('OWNER_REDIS_PASSWORD', null),
            'port'     => env('OWNER_REDIS_PORT', 6379),
            'database' => 14,
        ],
        'syncmd-clusters' => [
            // 'slave-1' => [
            //     'host'     => env('REDIS_HOST_SLAVE_1'),
            //     'password' => env('REDIS_PASSWORD_SLAVE_1', null),
            //     'port'     => env('REDIS_PORT_SLAVE_1', 6379),
            //     'database' => 14,
            // ],
            // 'slave-2' => [
            //     'host'     => env('REDIS_HOST_SLAVE_2'),
            //     'password' => env('REDIS_PASSWORD_SLAVE_2', null),
            //     'port'     => env('REDIS_PORT_SLAVE_2', 6379),
            //     'database' => 14,
            // ],
        ],

    ],

];
