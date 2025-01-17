<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Interceptor;

use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Cycle\DataGrid\Annotation\DataGrid;
use Spiral\Cycle\DataGrid\Response\GridResponseInterface;
use Spiral\DataGrid\GridFactory;
use Spiral\DataGrid\GridFactoryInterface;

/**
 * Automatically render grids using schema declared in annotation.
 *
 * @see DataGrid
 */
final class GridInterceptor implements CoreInterceptorInterface
{
    /** @var GridResponseInterface */
    private $response;

    /** @var ContainerInterface */
    private $container;

    /** @var GridFactory */
    private $gridFactory;

    /** @var array */
    private $cache = [];

    /**
     * @param GridResponseInterface $response
     * @param ContainerInterface    $container
     * @param GridFactory           $gridFactory
     */
    public function __construct(
        GridResponseInterface $response,
        ContainerInterface $container,
        GridFactory $gridFactory
    ) {
        $this->response = $response;
        $this->container = $container;
        $this->gridFactory = $gridFactory;
    }

    /**
     * @param string        $controller
     * @param string        $action
     * @param array         $parameters
     * @param CoreInterface $core
     * @return mixed
     * @throws \Throwable
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        $result = $core->callAction($controller, $action, $parameters);

        if (is_iterable($result)) {
            $schema = $this->getSchema($controller, $action);
            if ($schema !== null) {
                $factory = $this->makeFactory($schema);
                if (method_exists($factory, 'withDefaults')) {
                    $factory = $factory->withDefaults($schema['defaults']);
                }

                $grid = $factory->create($result, $schema['grid']);

                if ($schema['view'] !== null) {
                    $grid = $grid->withView($schema['view']);
                }

                return $this->response->withGrid($grid, $schema['options']);
            }
        }

        return $result;
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array|null
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function getSchema(string $controller, string $action): ?array
    {
        $key = sprintf('%s:%s', $controller, $action);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = null;
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return null;
        }

        $reader = new AnnotationReader();

        /** @var DataGrid $dataGrid */
        $dataGrid = $reader->getMethodAnnotation($method, DataGrid::class);
        if ($dataGrid === null) {
            return null;
        }

        return $this->cache[$key] = $this->makeSchema($dataGrid);
    }

    /**
     * @param DataGrid $dataGrid
     * @return array
     */
    private function makeSchema(DataGrid $dataGrid): array
    {
        $schema = [
            'grid'     => $this->container->get($dataGrid->grid),
            'view'     => $dataGrid->view,
            'options'  => $dataGrid->options,
            'defaults' => $dataGrid->defaults,
            'factory'  => $dataGrid->factory,
        ];

        if (is_string($schema['view'])) {
            $schema['view'] = $this->container->get($schema['view']);
        }

        if ($schema['defaults'] === [] && method_exists($schema['grid'], 'getDefaults')) {
            $schema['defaults'] = $schema['grid']->getDefaults();
        }

        if ($schema['options'] === [] && method_exists($schema['grid'], 'getOptions')) {
            $schema['options'] = $schema['grid']->getOptions();
        }

        if ($schema['view'] === null && is_callable($schema['grid'])) {
            $schema['view'] = $schema['grid'];
        }

        return $schema;
    }

    private function makeFactory(array $schema): GridFactoryInterface
    {
        if (!empty($schema['factory']) && $this->container->has($schema['factory'])) {
            $factory = $this->container->get($schema['factory']);
            if ($factory instanceof GridFactoryInterface) {
                return $factory;
            }
        }

        return $this->gridFactory;
    }
}
