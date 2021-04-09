<?php

namespace AlexGoncharCK\ChainCommandBundle;

use AlexGoncharCK\ChainCommandBundle\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ChainCommandBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new Compiler());
    }
}
