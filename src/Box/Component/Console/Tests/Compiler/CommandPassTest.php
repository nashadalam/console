<?php

namespace Box\Component\Console\Tests\Compiler;

use Box\Component\Console\Compiler\CommandPass;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CommandPassTest extends TestCase
{
    /**
     * The test container builder.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * The compiler pass to test.
     *
     * @var CommandPass
     */
    private $pass;

    /**
     * Verifies that the compiler pass registers our commands.
     *
     * @covers \Box\Component\Console\Compiler\CommandPass::process
     * @covers \Box\Component\Console\Compiler\CommandPass::registerCommand
     */
    public function testProcess()
    {
        $definition = new Definition(
            'Box\Component\Console\Command\ContainerDebugCommand'
        );

        $definition->addTag('box.console.command');

        $this->container->setDefinition('test', $definition);
        $this->container->compile();

        /** @var Application $console */
        $console = $this->container->get('box.console');

        self::assertTrue($console->has('container:debug'));
    }

    /**
     * Verifies that an exception is thrown for abstract services.
     *
     * @covers \Box\Component\Console\Compiler\CommandPass::process
     * @covers \Box\Component\Console\Compiler\CommandPass::registerCommand
     */
    public function testProcessAbstract()
    {
        $definition = new Definition(
            'Box\Component\Console\Command\ContainerDebugCommand'
        );

        $definition->addTag('box.console.command');
        $definition->setAbstract(true);

        $this->container->setDefinition('test', $definition);

        $this->setExpectedException(
            'LogicException',
            'The service "test" is abstract, so it cannot be used as a command.'
        );

        $this->container->compile();
    }

    /**
     * Verifies that an exception is thrown for non-public services.
     *
     * @covers \Box\Component\Console\Compiler\CommandPass::process
     * @covers \Box\Component\Console\Compiler\CommandPass::registerCommand
     */
    public function testProcessNotPublic()
    {
        $definition = new Definition(
            'Box\Component\Console\Command\ContainerDebugCommand'
        );

        $definition->addTag('box.console.command');
        $definition->setPublic(false);

        $this->container->setDefinition('test', $definition);

        $this->setExpectedException(
            'LogicException',
            'The service "test" is not public, so it cannot be used as a command.'
        );

        $this->container->compile();
    }

    /**
     * Verifies that an exception is thrown non-command services.
     *
     * @covers \Box\Component\Console\Compiler\CommandPass::process
     * @covers \Box\Component\Console\Compiler\CommandPass::registerCommand
     */
    public function testProcessNotCommand()
    {
        $definition = new Definition('DateTime');
        $definition->addTag('box.console.command');

        $this->container->setDefinition('test', $definition);

        $this->setExpectedException(
            'LogicException',
            'The service "test" is not a subclass of "Symfony\Component\Console\Command\Command", so it cannot be used as a command.'
        );

        $this->container->compile();
    }

    /**
     * Creates a new compiler pass for testing.
     */
    protected function setUp()
    {
        $this->pass = new CommandPass();

        $this->container = new ContainerBuilder();
        $this->container->addCompilerPass($this->pass);
        $this->container->setDefinition(
            'box.console',
            new Definition('Symfony\Component\Console\Application')
        );
    }
}
