<?php

declare(strict_types=1);

namespace Spiral\Cycle\DataGrid;

use Spiral\DataGrid\InputInterface;
use Spiral\Http\Request\InputManager;

final class GridInput implements InputInterface
{
    private InputManager $input;

    public function __construct(InputManager $input)
    {
        $this->input = $input;
    }

    public function withNamespace(string $prefix): InputInterface
    {
        $input = clone $this;
        $input->input = $input->input->withPrefix($prefix);

        return $input;
    }

    public function hasValue(string $option): bool
    {
        return $this->input->input($option) !== null;
    }

    /**
     * @param mixed  $default
     * @return mixed|null
     */
    public function getValue(string $option, $default = null)
    {
        return $this->input->input($option, $default);
    }
}