<?php

namespace Box\Component\Console\Tests\Command;

use Box\Component\Console\Command\ContainerDebugCommand;
use Box\Component\Console\Helper\ContainerHelper;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionMethod;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ContainerDebugCommandTest extends TestCase
{
    /**
     * The command to test.
     *
     * @var ContainerDebugCommand
     */
    private $command;

    /**
     * The test container.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * The test helper set.
     *
     * @var HelperSet
     */
    private $helperSet;

    /**
     * Verifies that the container builder from the helper is used.
     *
     * @covers \Box\Component\Console\Command\ContainerDebugCommand::getContainerBuilder
     */
    public function testGetContainerBuilder()
    {
        $reflection = new ReflectionMethod(
            get_class($this->command),
            'getContainerBuilder'
        );

        $reflection->setAccessible(true);

        self::assertSame(
            $this->container,
            $reflection->invoke($this->command)
        );
    }

    /**
     * Creates a new instance of the command to test.
     */
    protected function setUp()
    {
        $this->command = new ContainerDebugCommand();
        $this->container = new ContainerBuilder();
        $this->helperSet = new HelperSet(
            array(
                new ContainerHelper($this->container)
            )
        );

        $this->command->setHelperSet($this->helperSet);
    }
}
