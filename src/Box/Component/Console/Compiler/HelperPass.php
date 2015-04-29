<?php

namespace Box\Component\Console\Compiler;

use LogicException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers helpers services with the helper set.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class HelperPass implements CompilerPassInterface
{
    /**
     * Registers all services tagged as "box.console.helper" with the application.
     *
     * @param ContainerBuilder $container The container.
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('box.console.helper');

        foreach ($ids as $id => $tags) {
            $this->registerHelper($container, $id);
        }
    }

    /**
     * Registers a helper service with the helper set.
     *
     * @param ContainerBuilder $container The container.
     * @param string           $id        The identifier for the helper service.
     *
     * @throws LogicException If the helper cannot be registered.
     */
    private function registerHelper(ContainerBuilder $container, $id)
    {
        $definition = $container->getDefinition($id);

        if ($definition->isAbstract()) {
            throw new LogicException(
                sprintf(
                    'The service "%s" is abstract, so it cannot be used as a helper.',
                    $id
                )
            );
        }

        if (!$definition->isPublic()) {
            throw new LogicException(
                sprintf(
                    'The service "%s" is not public, so it cannot be used as a helper.',
                    $id
                )
            );
        }

        $reflection = new ReflectionClass(
            $container
                ->getParameterBag()
                ->resolveValue($definition->getClass())
        );

        if (!$reflection->isSubclassOf('Symfony\Component\Console\Helper\Helper')) {
            throw new LogicException(
                sprintf(
                    'The service "%s" is not a subclass of "%s", so it cannot be used as a helper.',
                    $id,
                    'Symfony\Component\Console\Helper\Helper'
                )
            );
        }

        $container
            ->getDefinition('box.console.helper_set')
            ->addMethodCall(
                'set',
                array(new Reference($id))
            )
        ;
    }
}
