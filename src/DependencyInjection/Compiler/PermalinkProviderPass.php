<?php
/*
 * Permalink extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2017
 * @package    contao-permalink
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

namespace Agoat\PermalinkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Registers the permalink providers
 */
class PermalinkProviderPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

	
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('contao.permalink.generator')) {
            return;
        }

        $definition = $container->findDefinition('contao.permalink.generator');
		
        $provider = $container->findTaggedServiceIds('permalink.provider');

        foreach ($provider as $id=>$tags) {
			foreach ($tags as $attributes)
			{
				$definition->addMethodCall('addProvider', [new Reference($id), $attributes['context']]);
			}
        }
    }
}
