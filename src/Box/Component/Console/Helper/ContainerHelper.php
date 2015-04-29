<?php

namespace Box\Component\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provides access to the dependency injection container as a helper.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ContainerHelper extends Helper
{
    /**
     * The dependency injection container.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * Sets the dependency injection container.
     *
     * @param ContainerBuilder $container The container.
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the dependency injection container.
     *
     * @return ContainerBuilder The container.
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'container';
    }
}
