<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Config;

use Spiral\Core\InjectableConfig;

/**
 * Configuration for data grid bridge writers.
 */
final class GridConfig extends InjectableConfig
{
    public const CONFIG = 'dataGrid';

    /** @var array */
    protected $config = [
        'writers' => [],
    ];

    /**
     * @return array
     */
    public function getWriters(): array
    {
        return $this->config['writers'];
    }
}
