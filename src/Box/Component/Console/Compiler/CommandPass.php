<?php

namespace Box\Component\Console\Compiler;

use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers command services with the application.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CommandPass implements CompilerPassInterface
{
    /**
     * Registers all services tagged as "box.console.command" with the application.
     *
     * @param ContainerBuilder $container The container.
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('box.console.command');

        foreach ($ids as $id => $tags) {
            $this->registerCommand($container, $id);
        }
    }

    /**
     * Registers a command service with the console application.
     *
     * @param ContainerBuilder $container The container.
     * @param string           $id        The identifier for the command service.
     *
     * @throws LogicException If the command cannot be registered.
     */
    private function registerCommand(ContainerBuilder $container, $id)
    {
        $definition = $container->getDefinition($id);

        if ($definition->isAbstract()) {
            throw new LogicException(
                sprintf(
                    'The service "%s" is abstract, so it cannot be used as a command.',
                    $id
                )
            );
        }

        if (!$definition->isPublic()) {
            throw new LogicException(
                sprintf(
                    'The service "%s" is not public, so it cannot be used as a command.',
                    $id
                )
            );
        }

        $reflection = new ReflectionClass(
            $container
                ->getParameterBag()
                ->resolveValue($definition->getClass())
        );

        if (!$reflection->isSubclassOf('Symfony\Component\Console\Command\Command')) {
            throw new LogicException(
                sprintf(
                    'The service "%s" is not a subclass of "%s", so it cannot be used as a command.',
                    $id,
                    'Symfony\Component\Console\Command\Command'
                )
            );
        }

        $container
            ->getDefinition('box.console')
            ->addMethodCall('add', array(new Reference($id)))
        ;
    }
}
