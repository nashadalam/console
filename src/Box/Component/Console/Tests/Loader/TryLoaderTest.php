<?php

namespace Box\Component\Console\Tests\Loader;

use Box\Component\Console\Loader\TryLoader;
use Box\Component\Console\Tests\Test\SpecificLoader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Box\Component\Console\Loader\TryLoader::__construct
 */
class TryLoaderTest extends TestCase
{
    /**
     * The loader instance being tested.
     *
     * @var TryLoader
     */
    private $loader;

    /**
     * Verifies that we can make multiple attempts at loading a file.
     *
     * @covers \Box\Component\Console\Loader\TryLoader::addTry
     * @covers \Box\Component\Console\Loader\TryLoader::load
     */
    public function testLoad()
    {
        $this->loader->addTry(
            function ($resource, $type) {
                return array($resource . '.dist', $type);
            }
        );

        self::assertEquals('loaded', $this->loader->load('test.xml'));

        $this->setExpectedException(
            'Symfony\Component\Config\Exception\FileLoaderLoadException',
            'Cannot load resource "test.fail".'
        );

        $this->loader->load('test.fail');
    }

    /**
     * Verifies that we can make multiple attempts at check for support.
     *
     * @covers \Box\Component\Console\Loader\TryLoader::addTry
     * @covers \Box\Component\Console\Loader\TryLoader::supports
     */
    public function testSupports()
    {
        self::assertFalse($this->loader->supports('test.xml'));
        self::assertTrue($this->loader->supports('test.xml.dist'));

        $this->loader->addTry(
            function ($resource, $type) {
                return array($resource . '.dist', $type);
            }
        );

        self::assertTrue($this->loader->supports('test.xml'));
    }

    /**
     * Creates a new instance of the loader for testing.
     */
    protected function setUp()
    {
        $this->loader = new TryLoader(
            new SpecificLoader('test.xml.dist', null, 'loaded')
        );
    }
}
