<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;

final class BridgeBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        // Database
        DatabaseBootloader::class,
        MigrationsBootloader::class,

        // ORM
        SchemaBootloader::class,
        CycleOrmBootloader::class,
        AnnotatedBootloader::class,
        CommandBootloader::class,

        // DataGrid (Optional)
        DataGridBootloader::class,
    ];
}
