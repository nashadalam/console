<?php

namespace Box\Component\Console\Tests\Compiler;

use Box\Component\Console\Compiler\HelperPass;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class HelperPassTest extends TestCase
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
     * @var HelperPass
     */
    private $pass;

    /**
     * Verifies that the compiler pass registers our helpers.
     *
     * @covers \Box\Component\Console\Compiler\HelperPass::process
     * @covers \Box\Component\Console\Compiler\HelperPass::registerHelper
     */
    public function testProcess()
    {
        $definition = new Definition(
            'Box\Component\Console\Helper\ContainerHelper'
        );

        $definition->addArgument(new Reference('service_container'));
        $definition->addTag('box.console.helper');

        $this->container->setDefinition('test', $definition);
        $this->container->compile();

        /** @var HelperSet $console */
        $set = $this->container->get('box.console.helper_set');

        self::assertTrue($set->has('container'));
    }

    /**
     * Verifies that an exception is thrown for abstract services.
     *
     * @covers \Box\Component\Console\Compiler\HelperPass::process
     * @covers \Box\Component\Console\Compiler\HelperPass::registerHelper
     */
    public function testProcessAbstract()
    {
        $definition = new Definition(
            'Box\Component\Console\Helper\ContainerHelper'
        );

        $definition->addTag('box.console.helper');
        $definition->setAbstract(true);

        $this->container->setDefinition('test', $definition);

        $this->setExpectedException(
            'LogicException',
            'The service "test" is abstract, so it cannot be used as a helper.'
        );

        $this->container->compile();
    }

    /**
     * Verifies that an exception is thrown for non-public services.
     *
     * @covers \Box\Component\Console\Compiler\HelperPass::process
     * @covers \Box\Component\Console\Compiler\HelperPass::registerHelper
     */
    public function testProcessNotPublic()
    {
        $definition = new Definition(
            'Box\Component\Console\Helper\ContainerHelper'
        );

        $definition->addTag('box.console.helper');
        $definition->setPublic(false);

        $this->container->setDefinition('test', $definition);

        $this->setExpectedException(
            'LogicException',
            'The service "test" is not public, so it cannot be used as a helper.'
        );

        $this->container->compile();
    }

    /**
     * Verifies that an exception is thrown non-helper services.
     *
     * @covers \Box\Component\Console\Compiler\HelperPass::process
     * @covers \Box\Component\Console\Compiler\HelperPass::registerHelper
     */
    public function testProcessNotCommand()
    {
        $definition = new Definition('DateTime');
        $definition->addTag('box.console.helper');

        $this->container->setDefinition('test', $definition);

        $this->setExpectedException(
            'LogicException',
            'The service "test" is not a subclass of "Symfony\Component\Console\Helper\Helper", so it cannot be used as a helper.'
        );

        $this->container->compile();
    }

    /**
     * Creates a new compiler pass for testing.
     */
    protected function setUp()
    {
        $this->pass = new HelperPass();

        $this->container = new ContainerBuilder();
        $this->container->addCompilerPass($this->pass);
        $this->container->setDefinition(
            'box.console.helper_set',
            new Definition('Symfony\Component\Console\Helper\HelperSet')
        );
    }
}
