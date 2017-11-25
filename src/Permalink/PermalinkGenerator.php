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

namespace Agoat\PermalinkBundle\Permalink;

use Contao\DataContainer;


/**
 * Permalink generator
 */
class PermalinkGenerator
{

   /**
 	 * PermalinkProviderInterface
     * @var array
     */
	private $providers;

   /**
 	 * Context
     * @var array
     */
	private $context;

	
    /**
	 * Constructor
	 */
	public function __construct()
	{
		$this->providers = array();
		$this->context = array();
	}

	
    /**
	 * Register provider
	 *
     * @param PermalinkProviderInterface $provider
	 * @param string                     $context
     */
	public function addProvider(PermalinkProviderInterface $provider, $context)
	{
		$this->providers[$context] = $provider;
		$this->context[$provider->getDcaTable()] = $context;
	}
	
	
    /**
	 * Returns whether the given table is supported
	 *
     * @param string $table
	 *
	 * @return boolean
     */
	public function supportsTable($table)
	{
		return array_key_exists($table, $this->context);
	}

	
    /**
	 * Return all registered providers
	 *
	 * @return array
     */
	public function getProviders()
	{
		return $this->providers;
	}

	
    /**
	 * Return the context for the given table
	 *
     * @param string $table
	 *
	 * @return string|Null
     */
	public function getContextForTable($table)
	{
		return $this->context[$table];
	}

	
    /**
	 * Generate the permalink for the current object
	 *
     * @param DataContainer $dc
	 *
	 * @throws AccessDeniedException
     */
	public function generate(DataContainer $dc)
	{
		$context = $this->context[$dc->table];
		
		return $this->providers[$context]->generate($context, $dc->id);
	}

	
    /**
	 * Remove the permalink of the current object
	 *
     * @param DataContainer $dc
     */
	public function remove(DataContainer $dc)
	{
		$context = $this->context[$dc->table];
		
		return $this->providers[$context]->remove($context, $dc->id);
	}

	
    /**
	 * Return the url for the current object
	 *
     * @param DataContainer $dc
	 *
     * @return PermalinkUrl
     */
	public function getUrl(DataContainer $dc)
	{
		$context = $this->context[$dc->table];
		
		return $this->providers[$context]->getUrl($context, $dc->id);
	}	
}
