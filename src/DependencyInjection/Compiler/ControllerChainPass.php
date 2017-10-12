<?php
 
 /**
 * Contao Open Source CMS - Permalink extension
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   permalink
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the picker providers.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ControllerChainPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('permalink.controller.chain')) {
            return;
        }

        $definition = $container->findDefinition('permalink.controller.chain');
		
        $controllers = $container->findTaggedServiceIds('permalink.controller');

        foreach ($controllers as $id=>$tags) {
			foreach ($tags as $attributes)
			{
				$definition->addMethodCall('addController', [new Reference($id), $attributes['context']]);
			}
        }
    }
}