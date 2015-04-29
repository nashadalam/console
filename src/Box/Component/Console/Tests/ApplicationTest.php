<?php

namespace Box\Component\Console\Tests;

use Box\Component\Console\Application;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
     * @covers \Box\Component\Console\Application::setHelperDefinition
     * @covers \Box\Component\Console\Application::setHelperDefinitions
     * @covers \Box\Component\Console\Application::setHelperSetDefinition
     */
    public function testRun()
    {
        $this->application->load(
            function (ContainerBuilder $container) {
                $container->setParameter('box.console.auto_exit', false);
            }
        );

        $input = new ArrayInput(
            array(
                'command' => 'container:debug'
            )
        );

        $stream = fopen('php://memory', 'r+');
        $output = new StreamOutput($stream);

        self::assertEquals(
            0,
            $this->application->run($input, $output)
        );

        rewind($stream);

        self::assertContains(
            'box.console',
            fread($stream, 9999)
        );

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
