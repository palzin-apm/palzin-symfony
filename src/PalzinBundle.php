<?php

namespace Palzin\Symfony\Bundle;

use Palzin\Symfony\Bundle\DependencyInjection\Compiler\DoctrineDBALCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PalzinBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineDBALCompilerPass());
    }
}
