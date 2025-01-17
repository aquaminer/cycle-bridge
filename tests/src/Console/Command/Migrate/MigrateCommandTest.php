<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\Migrate;

use Cycle\Database\DatabaseInterface;
use Spiral\Tests\ConsoleTestCase;

final class MigrateCommandTest extends ConsoleTestCase
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public function testMigrate(): void
    {
        /** @var DatabaseInterface $db */
        $db = $this->app->get(DatabaseInterface::class);
        $this->assertSame([], $db->getTables());

        $this->runCommandDebug('migrate:init');
        $this->runCommandDebug('cycle:migrate');

        $this->assertCount(1, $db->getTables());

        $this->runCommandDebug('migrate');
        $this->assertCount(3, $db->getTables());
    }
}
