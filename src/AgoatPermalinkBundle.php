<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
namespace Agoat\PermalinkBundle;

use Agoat\PermalinkBundle\DependencyInjection\Compiler\FrontendControllerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Configures the Agoat contentblocks bundle.
 *
 * @author Arne Stappen (alias aGoat) <https://github.com/agoat>
 */
class AgoatPermalinkBundle extends Bundle
{
   public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FrontendControllerPass());
    }
}