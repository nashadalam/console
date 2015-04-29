<?php

namespace Box\Component\Console\Tests;

use Box\Component\Console\Application;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Console\Application::__construct
 */
class ApplicationTest extends TestCase
{
    /**
     * The application instance being tested.
     *
     * @var Application
     */
    private $application;

    /**
     * Verifies that application runs.
     *
     * @covers \Box\Component\Console\Application::createBuilder
     * @covers \Box\Component\Console\Application::createLoader
     * @covers \Box\Component\Console\Application::load
     * @covers \Box\Component\Console\Application::run
     * @covers \Box\Component\Console\Application::setConsoleDefinition
     * @covers \Box\Component\Console\Application::setContainerDefinitions
     * @covers \Box\Component\Console\Application::setEventDispatcherDefinition
     * @covers \Box\Component\Console\Application::setHelperDefinition
     * @covers \Box\Component\Console\Application::setHelperDefinitions
     * @covers \Box\Component\Console\Application::setHelperSetDefinition
     */
    public function testRun()
    {
        $this->application->load(
            function (ContainerBuilder $container) {
                $container->setParameter('box.console.auto_exit', false);

                $listener = new Definition(
                    'Box\Component\Console\Tests\Test\EventListener'
                );

                $listener->addTag(
                    'box.console.event.listener',
                    array(
                        'event' => ConsoleEvents::TERMINATE,
                        'method' => 'sayHello'
                    )
                );

                $container->setDefinition('test_listener', $listener);
            }
        );

        $input = new ArrayInput(
            array(
                'command' => 'container:debug'
            )
        );

        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);
        $status = $this->application->run($input, $output);

        rewind($stream);

        $output = fread($stream, 9999);

        self::assertContains('box.console', $output);
        self::assertContains('Hello, world!', $output);
        self::assertEquals(0, $status);

        fclose($stream);
    }

    /**
     * Creates a new instance of the application for testing.
     */
    protected function setUp()
    {
        $this->application = new Application();
    }
}
