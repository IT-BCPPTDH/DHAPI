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
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'MSADMIN' => [
            'driver'         => 'oracle',
            'tns'            => env('DB_TNS', ''),
            'host'           => env('DB_HOST', ''),
            'port'           => env('DB_PORT', '1521'),
            'database'       => env('DB_DATABASE', ''),
            'service_name'   => env('DB_SERVICE_NAME', ''),
            'username'       => env('DB_USERNAME', ''),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => env('DB_CHARSET', 'AL32UTF8'),
            'prefix'         => env('DB_PREFIX', ''),
            'prefix_schema'  => env('DB_SCHEMA_PREFIX', ''),
            'edition'        => env('DB_EDITION', 'ora$base'),
            'server_version' => env('DB_SERVER_VERSION', '11g'),
            'load_balance'   => env('DB_LOAD_BALANCE', 'yes'),
            'dynamic'        => [],
        ],

        'BCPDWHS' => [
            'driver'         => 'oracle',
            'tns'            => env('DB_TNS_BCPDWHS', ''),
            'host'           => env('DB_HOST_BCPDWHS', ''),
            'port'           => env('DB_PORT_BCPDWHS', '1521'),
            'database'       => env('DB_DATABASE_BCPDWHS', ''),
            'service_name'   => env('DB_SERVICE_NAME_BCPDWHS', ''),
            'username'       => env('DB_USERNAME_BCPDWHS', ''),
            'password'       => env('DB_PASSWORD_BCPDWHS', ''),
            'charset'        => env('DB_CHARSET_BCPDWHS', 'AL32UTF8'),
            'prefix'         => env('DB_PREFIX_BCPDWHS', ''),
            'prefix_schema'  => env('DB_SCHEMA_PREFIX_BCPDWHS', ''),
            'edition'        => env('DB_EDITION_BCPDWHS', 'ora$base'),
            'server_version' => env('DB_SERVER_VERSION_BCPDWHS', '11g'),
            'load_balance'   => env('DB_LOAD_BALANCE_BCPDWHS', 'yes'),
            'dynamic'        => [],
        ],

        'ELLIPSE' => [
            'driver'         => 'oracle',
            'tns'            => env('DB_TNS_ELLIPSE', ''),
            'host'           => env('DB_HOST_ELLIPSE', ''),
            'port'           => env('DB_PORT_ELLIPSE', '1521'),
            'database'       => env('DB_DATABASE_ELLIPSE', ''),
            'service_name'   => env('DB_SERVICE_NAME_ELLIPSE', ''),
            'username'       => env('DB_USERNAME_ELLIPSE', ''),
            'password'       => env('DB_PASSWORD_ELLIPSE', ''),
            'charset'        => env('DB_CHARSET_ELLIPSE', 'AL32UTF8'),
            'prefix'         => env('DB_PREFIX_ELLIPSE', ''),
            'prefix_schema'  => env('DB_SCHEMA_PREFIX_ELLIPSE', ''),
            'edition'        => env('DB_EDITION_ELLIPSE', 'ora$base'),
            'server_version' => env('DB_SERVER_VERSION_ELLIPSE', '11g'),
            'load_balance'   => env('DB_LOAD_BALANCE_ELLIPSE', 'yes'),
            'dynamic'        => [],
        ],

        'sqlsrv_sql_bcp' => [
            'driver' => 'sqlsrv',
            'host' => '10.29.44.1',
            'port' => '1433',
            'database' => 'SNCMineLink_DH1',
            'username' => 'developer',
            'password' => 'Minelink!12',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'oracle_bcp' => [
            'driver'         => 'oracle',
            // 'tns'            => 'BCPDWHS',
            'host'           => '10.29.4.17',
            'port'           => '1521',
            'database'       => 'ICMA',
            'service_name'   => 'bcpdwhs',
            'username'       => 'icma',
            'password'       => 'rahasia123',
            'charset'        => 'AL32UTF8',
            'prefix'         => '',
            'prefix_schema'  => '',
            'edition'        => 'ora$base',
            'server_version' => '11g',
            'load_balance'   => 'yes',
            'dynamic'        => [],
        ],
        'CrystalDH' => [
            'driver' => 'sqlsrv',
            'host' => '10.27.240.203',
            'port' => '1433',
            'database' => 'Crystal_DH',
            'username' => 'it',
            'password' => '123abc',
            'charset' => 'utf8',
            'prefix' => '',
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

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
