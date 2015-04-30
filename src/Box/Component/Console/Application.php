<?php

namespace Box\Component\Console;

use Box\Component\Console\Compiler\CommandPass;
use Box\Component\Console\Compiler\HelperPass;
use Box\Component\Console\Loader\TryLoader;
use ReflectionMethod;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

/**
 * A console application managed by a dependency injection container.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Application
{
    /**
     * The console application.
     *
     * @var ConsoleApplication
     */
    private $application;

    /**
     * The container builder.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * The resource loader.
     *
     * @var TryLoader
     */
    private $loader;

    /**
     * Initializes the container builder.
     *
     * @param string $name    The name of the application.
     * @param string $version The version of the application.
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->container = $this->createBuilder($name, $version);
        $this->loader = $this->createLoader($this->container);
    }

    /**
     * Returns the configuration loader.
     *
     * @return TryLoader The configuration loader.
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Loads a configuration resource.
     *
     * @param mixed       $resource The resource to load.
     * @param null|string $type     The type of the resource.
     *
     * @return Application For method chaining.
     */
    public function load($resource, $type = null)
    {
        $this->loader->load($resource, $type);

        return $this;
    }

    /**
     * Runs the console application.
     *
     * The first time this method is called will trigger a compile on the
     * dependency injection container builder. The `box.console` service
     * will then be used from the container.
     *
     * @param InputInterface  $input  The input manager.
     * @param OutputInterface $output The output manager.
     *
     * @return integer The command exit status.
     */
    public function run(
        InputInterface $input = null,
        OutputInterface $output = null
    ) {
        if (null === $this->application) {
            $this->container->compile();

            $this->application = $this->container->get('box.console');
        }

        return $this->application->run($input, $output);
    }

    /**
     * Creates a new container builder.
     *
     * @param string $name    The name of the application.
     * @param string $version The version of the application.
     *
     * @return ContainerBuilder The new container builder.
     */
    private function createBuilder($name, $version)
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new CommandPass());
        $container->addCompilerPass(new HelperPass());
        $container->addCompilerPass(
            new RegisterListenersPass(
                'box.console.event_dispatcher',
                'box.console.event.listener',
                'box.console.event.subscriber'
            )
        );
        $container->setParameter('box.console.name', $name);
        $container->setParameter('box.console.version', $version);

        $this->setConsoleDefinition($container);
        $this->setContainerDefinitions($container);
        $this->setEventDispatcherDefinition($container);
        $this->setHelperDefinitions($container);
        $this->setHelperSetDefinition($container);

        return $container;
    }

    /**
     * Creates a new delegating resource loader.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return DelegatingLoader The new resource loader.
     */
    private function createLoader(ContainerBuilder $container)
    {
        $locator = new FileLocator(array());

        return new TryLoader(
            new DelegatingLoader(
                new LoaderResolver(
                    array(
                        new ClosureLoader($container, $locator),
                        new IniFileLoader($container, $locator),
                        new PhpFileLoader($container, $locator),
                        new XmlFileLoader($container, $locator),
                        new YamlFileLoader($container, $locator)
                    )
                )
            )
        );
    }

    /**
     * Sets the default console application definition.
     *
     * @param ContainerBuilder $container The container builder.
     */
    private function setConsoleDefinition(ContainerBuilder $container)
    {
        $container->setParameter('box.console.auto_exit', true);
        $container->setParameter(
            'box.console.class',
            'Symfony\Component\Console\Application'
        );

        $definition = new Definition('%box.console.class%');
        $definition->addArgument('%box.console.name%');
        $definition->addArgument('%box.console.version%');
        $definition->addMethodCall(
            'setAutoExit',
            array(
                '%box.console.auto_exit%'
            )
        );

        $container->setDefinition('box.console', $definition);
    }

    /**
     * Sets our command and helper used for the container.
     *
     * @param ContainerBuilder $container The container builder.
     */
    private function setContainerDefinitions(ContainerBuilder $container)
    {
        $container->setParameter(
            'box.console.command.container_debug.class',
            'Box\Component\Console\Command\ContainerDebugCommand'
        );

        $command = new Definition('%box.console.command.container_debug.class%');
        $command->addTag('box.console.command');

        $container->setDefinition(
            'box.console.command.container_debug',
            $command
        );

        $container->setParameter(
            'box.console.helper.container.class',
            'Box\Component\Console\Helper\ContainerHelper'
        );

        $helper = new Definition('%box.console.helper.container.class%');
        $helper->addArgument(new Reference('service_container'));
        $helper->addTag('box.console.helper');

        $container->setDefinition('box.console.helper.container', $helper);
    }

    /**
     * Sets the definition for the event dispatcher.
     *
     * @param ContainerBuilder $container The container builder.
     */
    private function setEventDispatcherDefinition(ContainerBuilder $container)
    {
        $container->setParameter(
            'box.console.event_dispatcher.class',
            'Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher'
        );

        $definition = new Definition('%box.console.event_dispatcher.class%');
        $definition->addArgument(new Reference('service_container'));

        $container->setDefinition(
            'box.console.event_dispatcher',
            $definition
        );

        $container
            ->getDefinition('box.console')
            ->addMethodCall(
                'setDispatcher',
                array(
                    new Reference('box.console.event_dispatcher')
                )
            )
        ;
    }

    /**
     * Sets the definition for an individual helper.
     *
     * @param ContainerBuilder $container The container builder.
     * @param string           $name             The name of the helper.
     * @param string           $class            The helper class.
     */
    private function setHelperDefinition(
        ContainerBuilder $container,
        $name,
        $class
    ) {
        $container->setParameter("box.console.helper.$name.class", $class);

        $container->setDefinition(
            "box.console.helper.$name",
            new Definition("%box.console.helper.$name.class%")
        );
    }

    /**
     * Sets the default helper definitions.
     *
     * The default list of helpers is pulled directly from the available
     * `Symfony\Component\Console\Application` class. A new instance is created
     * without instantiating it, and the helpers in the helper set returned by
     * the `getDefaultHelperSet` are re-created in the container.
     *
     * @param ContainerBuilder $container The container builder.
     */
    private function setHelperDefinitions(ContainerBuilder $container)
    {
        $reflection = new ReflectionMethod(
            $container->getParameter('box.console.class'),
            'getDefaultHelperSet'
        );

        $reflection->setAccessible(true);

        $set = $reflection->invoke(
            $reflection
                ->getDeclaringClass()
                ->newInstanceWithoutConstructor()
        );

        foreach ($set as $name => $helper) {
            $this->setHelperDefinition(
                $container,
                $name,
                get_class($helper)
            );
        }
    }

    /**
     * Sets the default command helper set definition.
     *
     * @param ContainerBuilder $container The container builder.
     */
    private function setHelperSetDefinition(ContainerBuilder $container)
    {
        $container->setParameter(
            'box.console.helper_set.class',
            'Symfony\Component\Console\Helper\HelperSet'
        );

        $definition = new Definition('%box.console.helper_set.class%');

        $container->setDefinition('box.console.helper_set', $definition);

        $container
            ->getDefinition('box.console')
            ->addMethodCall(
                'setHelperSet',
                array(
                    new Reference('box.console.helper_set')
                )
            )
        ;
    }
}
