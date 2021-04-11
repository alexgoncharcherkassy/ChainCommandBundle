<?php

namespace AlexGoncharCK\ChainCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class Compiler
 */
class Compiler implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('chain_command_bundle.chain_manager');
        $taggedServices = $container->findTaggedServiceIds('chain_command');
        $commands = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                $commands[] = [
                    'command' => new Reference($id),
                    'master' => (bool)($tag['master'] ?? false),
                    'parent' => $tag['parent'] ?? null
                ];
            }
        }
        $definition->addMethodCall('addCommands', [$commands]);
    }
}
