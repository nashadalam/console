<?php

namespace Box\Component\Console\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerDebugCommand as Base;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Uses a container from a helper as opposed to an HTTP kernel.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ContainerDebugCommand extends Base
{
    /**
     * Returns the container builder from a helper.
     *
     * @return ContainerBuilder The container builder.
     */
    protected function getContainerBuilder()
    {
        return $this->getHelper('container')->getContainer();
    }
}
