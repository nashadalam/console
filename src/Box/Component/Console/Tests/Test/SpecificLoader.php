<?php

namespace Box\Component\Console\Tests\Test;

use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Loader\Loader;

/**
 * Returns data for a specific combination of parameters.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class SpecificLoader extends Loader
{
    /**
     * The expected resource.
     *
     * @var mixed
     */
    private $resource;

    /**
     * The expected resource type.
     *
     * @var null|string
     */
    private $type;

    /**
     * The value to return on load.
     *
     * @var mixed
     */
    private $value;

    /**
     * Sets the expected resource and resource type.
     *
     * @param mixed       $resource The expected resource.
     * @param null|string $type     The expected resource type.
     * @param mixed       $value   The value to return on load.
     */
    public function __construct($resource, $type, $value)
    {
        $this->resource = $resource;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if ($this->supports($resource, $type)) {
            return $this->value;
        }

        throw new FileLoaderLoadException($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return (($this->resource === $resource) && ($this->type === $type));
    }
}
