<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class DataGrid
{
    /**
     * Points to grid schema.
     *
     * @Attribute(name="grid", type="string", required=true)
     * @type string
     */
    public $grid;

    /**
     * Response options, default to GridSchema->__invoke() if such method exists.
     *
     * @Attribute(name="view", type="string")
     * @var string
     */
    public $view;

    /**
     * Response options, default to GridSchema->getDefaults() if such method exists.
     *
     * @Attribute(name="defaults", type="array")
     * @var array
     */
    public $defaults = [];

    /**
     * Response options, default to GridSchema->getOptions() if such method exists.
     *
     * @Attribute(name="options", type="array")
     * @var array
     */
    public $options = [];

    /**
     * Custom user GridFactory
     *
     * @Attribute(name="factory", type="string")
     * @var string
     */
    public $factory;
}
