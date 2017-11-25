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

namespace Agoat\PermalinkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Permalink controller chain
 */
class ControllerChain
{

    /**
	 * ControllerInterface
     * @var array
     */
	private $controllers;

	
    /**
	 * Constructor
	 */
	public function __construct()
	{
		$this->controllers = array();
	}

	
    /**
	 * Register controller
	 *
     * @param ControllerInterface $controller
	 * @param string              $context
     */
	public function addController(ControllerInterface $controller, $context)
	{
		$this->controllers[$context] = $controller;
	}


    /**
	 * Return a controller for the context
	 *
     * @param string $context
	 *
	 * @return ControllerInterface|Null
     */
	public function getController($context)
	{
		if (array_key_exists($context, $this->controllers)) 
		{
			return $this->controllers[$context];
		}
	}


    /**
	 * Return all registered controllers
	 *
	 * @return array
     */
	public function getControllers()
	{
		return $this->controllers;
	}


    /**
	 * Return all contexts with controllers
	 *
	 * @return array
     */
	public function getContexts()
	{
		return array_keys($this->controllers);
	}
}
