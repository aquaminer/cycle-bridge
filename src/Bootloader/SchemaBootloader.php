<?php

declare(strict_types=1);

namespace Spiral\Cycle\Bootloader;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\TokenizerBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Cycle\Schema\Compiler;

final class SchemaBootloader extends Bootloader implements Container\SingletonInterface
{
    public const GROUP_INDEX = 'index';
    public const GROUP_RENDER = 'render';
    public const GROUP_POSTPROCESS = 'postprocess';

    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
        CycleOrmBootloader::class,
    ];

    protected const BINDINGS = [
        SchemaInterface::class => [self::class, 'schema'],
    ];

    /** @var string[][]|GeneratorInterface[][] */
    private array $generators = [];

    public function __construct(
        private ConfiguratorInterface $config,
        private Container $container
    ) {
        $this->generators = [
            self::GROUP_INDEX => [
                // find available entities
            ],
            self::GROUP_RENDER => [
                // render tables and relations
                Generator\ResetTables::class,
                Generator\GenerateRelations::class,
                Generator\ValidateEntities::class,
                Generator\RenderTables::class,
                Generator\RenderRelations::class,
            ],
            self::GROUP_POSTPROCESS => [
                // post processing
                Generator\GenerateTypecast::class,
            ],
        ];
    }

    public function addGenerator(string $group, string $generator): void
    {
        $this->generators[$group][] = $generator;
    }

    /**
     * @return GeneratorInterface[]
     * @throws \Throwable
     */
    public function getGenerators(): array
    {
        $result = [];
        foreach ($this->generators as $group) {
            foreach ($group as $generator) {
                if (is_object($generator) && ! $generator instanceof Container\Autowire) {
                    $result[] = $generator;
                } else {
                    $result[] = $this->container->get($generator);
                }
            }
        }

        return $result;
    }

    /**
     * @throws \Throwable
     */
    protected function schema(MemoryInterface $memory): SchemaInterface
    {
        $schemaCompiler = Compiler::fromMemory($memory);
        if ($schemaCompiler->isEmpty()) {
            $schemaCompiler = Compiler::compile(
                $this->container->get(Registry::class),
                $this->getGenerators(),
                $this->config->getConfig('cycle')['schema']['defaults'] ?? []
            );

            $schemaCompiler->toMemory($memory);
        }

        return $schemaCompiler->toSchema();
    }
}
