<?php

namespace Dtc\GridBundle;

use Dtc\GridBundle\DependencyInjection\Compiler\GridSourceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DtcGridBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new GridSourceCompilerPass());
    }
}
