<?php

/*
 * This file is part of the permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Frontend;

use Agoat\PermalinkBundle\Frontend\ControllerInterface;
use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Handles the Contao frontend routes.
 *
 * @author Arne Stappen <https://github.com/agoat>
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class ControllerChain
{
    /**
     * @var array
     */
	private $controllers;

	
	public function __construct()
	{
		$this->controllers = array();
	}

	
	public function addController(ControllerInterface $controller, $context)
	{
		$this->controllers[$context] = $controller;
	}

	public function getController($context)
	{
		if (array_key_exists($context, $this->controllers)) 
		{
			return $this->controllers[$context];
		}
	}
}