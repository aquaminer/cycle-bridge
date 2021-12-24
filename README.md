# Cycle ORM v2 bridge to Spiral Framework

[![Latest Stable Version](https://poser.pugx.org/spiral/cycle-bridge/version)](https://packagist.org/packages/spiral/cycle-bridge)
[![Build Status](https://github.com/spiral/cycle-bridge/workflows/build/badge.svg)](https://github.com/spiral/cycle-bridge/actions)
[![Codecov](https://codecov.io/gh/spiral/cycle-bridge/graph/badge.svg)](https://codecov.io/gh/spiral/cycle-bridge)

-----

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.0+
- PDO Extension with desired database drivers

## Installation

To install the package:

```bash
composer require spiral/cycle-bridge
```

After package install you need to add bootloaders from the package in your application.

```php
use Spiral\Cycle\Bootloader as CycleBridge;

protected const LOAD = [
    // ...

    // Database
    CycleBridge\DatabaseBootloader::class,
    CycleBridge\MigrationsBootloader::class,

    // ORM
    CycleBridge\SchemaBootloader::class,
    CycleBridge\CycleOrmBootloader::class,
    CycleBridge\AnnotatedBootloader::class,
    CycleBridge\CommandBootloader::class,
    
    // DataGrid (Optional)
    CycleBridge\DataGridBootloader::class,
];
```

If you are migration from Spiral Framework 2.8 you need to replace some of bootloaders with provided by the package.

```php
use Spiral\Cycle\Bootloader as CycleBridge;

protected const LOAD = [
    // ...

    // Databases
    // OLD
    // Framework\Database\DatabaseBootloader::class,
    // Framework\Database\MigrationsBootloader::class,
    
    // NEW
    CycleBridge\DatabaseBootloader::class,
    CycleBridge\MigrationsBootloader::class,

    // ORM
    // OLD
    // Framework\Cycle\CycleBootloader::class,
    // Framework\Cycle\ProxiesBootloader::class,
    // Framework\Cycle\AnnotatedBootloader::class,

    // NEW
    CycleBridge\SchemaBootloader::class,
    CycleBridge\CycleOrmBootloader::class,
    CycleBridge\AnnotatedBootloader::class,
    CycleBridge\CommandBootloader::class,
    
    // ...
    
    // DataGrid (Optional)
    // OLD
    // \Spiral\DataGrid\Bootloader\GridBootloader::class,
    
    // NEW
    CycleBridge\DataGridBootloader::class,
];
```

## Configuration

#### Database

You can create config file `app/config/database.php` if you want to configure Cycle Database:

```php
return [
    /**
     * Database logger configuration
     */
    'logger' => [
        'default' => null,
        'drivers' => [
            'sqlite' => 'file' // Log channel for Sq
        ],
    ],
     
    /**
     * Default database connection
     */
    'default' => env('DB_DEFAULT', 'default'),

    /**
     * The Cycle/Database module provides support to manage multiple databases
     * in one application, use read/write connections and logically separate
     * multiple databases within one connection using prefixes.
     *
     * To register a new database simply add a new one into
     * "databases" section below.
     */
    'databases' => [
        'default' => [
            'driver' => 'sqlite',
        ],
    ],

    /**
     * Each database instance must have an associated connection object.
     * Connections used to provide low-level functionality and wrap different
     * database drivers. To register a new connection you have to specify
     * the driver class and its connection options.
     */
    'drivers' => [
        'sqlite' => new Database\Config\SQLiteDriverConfig(
            connection: new Database\Config\SQLite\FileConnectionConfig(
                database: env('DB_DATABASE', directory('root') . 'runtime/app.db')
            ),
            queryCache: true,
        ),
        // ...
    ],
];
```

#### Cycle ORM

You can create config file `app/config/cycle.php` if you want to configure Cycle ORM:

```php
use Cycle\ORM\SchemaInterface;

return [
    'schema' => [
        /**
         * The CycleORM provides the ability to manage default settings for 
         * every schema with not defined segments
         */
        'defaults' => [
            SchemaInterface::MAPPER => \Cycle\ORM\Mapper\Mapper::class,
            SchemaInterface::REPOSITORY => \Cycle\ORM\Select\Repository::class,
            SchemaInterface::SCOPE => null,
            SchemaInterface::TYPECAST_HANDLER => [
                \Cycle\ORM\Parser\Typecast::class
            ],
        ]
    ],
];
```

#### Migrations

You can create config file `app/config/migrations.php` if you want to configure Cycle ORM Migrations:

```php
return [
    /**
     * Directory to store migration files
     */
    'directory' => directory('application').'migrations/',

    /**
     * Table name to store information about migrations status (per database)
     */
    'table' => 'migrations',

    /**
     * When set to true no confirmation will be requested on migration run.
     */
    'safe' => env('SPIRAL_ENV') == 'develop',
];
```
