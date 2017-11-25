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

namespace Agoat\PermalinkBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;


/**
 * Adds the bundle services to the container
 */
class AgoatPermalinkExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
		
        $loader->load('services.yml');

		$this->setDefaultParameters($container);
	}
	  
 
	/**
     * Set the default permalink paramters
     */
	private function setDefaultParameters(ContainerBuilder $container)
	{
		if (!$container->hasParameter('contao.permalink.page'))
		{
			$container->setParameter('contao.permalink.page', '{{parent+/}}{{alias}}');
		}
		
		if (!$container->hasParameter('contao.permalink.items'))
		{
			$container->setParameter('contao.permalink.items', '{{parent+/}}{{alias}}');
		}
		  
		if (!$container->hasParameter('contao.permalink.events'))
		{
			$container->setParameter('contao.permalink.events', '{{date}}/{{alias}}');
		}
	}
}
