<?php

namespace AlexGoncharCK\ChainCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

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
        $definition->addMethodCall('addCommands', [$taggedServices]);
    }
}
