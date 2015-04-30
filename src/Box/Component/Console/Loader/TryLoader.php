<?php

namespace Box\Component\Console\Loader;

use Exception;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Loader that tries different resources and types.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class TryLoader extends Loader
{
    /**
     * The loader.
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * The try callbacks.
     *
     * @var callable[]
     */
    private $tries = array();

    /**
     * Sets the loader.
     *
     * @param LoaderInterface $loader The loader.
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Adds a try callback.
     *
     * @param callable $try The try callback.
     *
     * @return TryLoader For method chaining.
     */
    public function addTry(callable $try)
    {
        $this->tries[] = $try;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        try {
            return $this->loader->load($resource, $type);
        } catch (Exception $exception) {
            foreach ($this->tries as $try) {
                try {
                    list($resource, $type) = $try($resource, $type);

                    return $this->loader->load($resource, $type);
                } catch (Exception $other) {
                }
            }
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        if ($this->loader->supports($resource, $type)) {
            return true;
        }

        foreach ($this->tries as $try) {
            list($resource, $type) = $try($resource, $type);

            if ($this->loader->supports($resource, $type)) {
                return true;
            }
        }

        return false;
    }
}
