<?php

namespace Box\Component\Console\Tests\Helper;

use Box\Component\Console\Helper\ContainerHelper;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Console\Helper\ContainerHelper::__construct
 */
class ContainerHelperTest extends TestCase
{
    /**
     * The test container builder.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * The helper being tested.
     *
     * @var ContainerHelper
     */
    private $helper;

    /**
     * Verifies that we can retrieve the container.
     *
     * @covers \Box\Component\Console\Helper\ContainerHelper::getContainer
     */
    public function testGetContainer()
    {
        self::assertSame($this->container, $this->helper->getContainer());
    }

    /**
     * Verifies the name of the helper.
     *
     * @covers \Box\Component\Console\Helper\ContainerHelper::getName
     */
    public function testGetName()
    {
        self::assertEquals('container', $this->helper->getName());
    }

    /**
     * Creates a new instance of the helper for testing.
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->helper = new ContainerHelper($this->container);
    }
}
