<?php

/*
 * This file is part of the permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Controller;

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
class RoutingController extends Controller
{
    /**
     * Fetch and run the responsible contoller form the database
     *
     * @return Response
     */
    public function indexAction($alias, Request $request)
    {
		$stopwatch = $this->get('debug.stopwatch');
		
		$stopwatch->start('routing');

		// First try to find an url entry directly
		$objPermalink = \PermalinkModel::findByGuid($request->getHost() . '/' . $alias);

		// Secondly try to find a parent url entry
		while (null === $objPermalink && strpos($alias, '/') !== false)
		{
			$arrFragments[] = basename($alias);
			$alias = dirname($alias);

			$objPermalink = \PermalinkModel::findByGuid($request->getHost() . '/' . $alias);
		}
		
		if (null === $objPermalink)
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri());
		}
		
		$stopwatch->stop('routing');
		
		// TODO: generate setGet fragments
		if (!empty($arrFragments))
		{
			$arrFragments = array_reverse($arrFragments);
			array_unshift($arrFragments, $alias);
		
			// Add the second fragment as auto_item if the number of fragments is even
			if (\Config::get('useAutoItem') && count($arrFragments) % 2 == 0)
			{
				array_insert($arrFragments, 1, array('auto_item'));
			}
			
			// Add the fragments to the $_GET array
			for ($i=1, $c=count($arrFragments); $i<$c; $i+=2)
			{
				// Skip key value pairs if the key is empty (see #4702)
				if ($arrFragments[$i] == '')
				{
					continue;
				}
				
				// Return false if there is a duplicate parameter (duplicate content) (see #4277)
				if (isset($_GET[$arrFragments[$i]]))
				{
					return false;
				}
				
				// Return false if the request contains an auto_item keyword (duplicate content) (see #4012)
				if (\Config::get('useAutoItem') && in_array($arrFragments[$i], $GLOBALS['TL_AUTO_ITEM']))
				{
					return false;
				}
				
				\Input::setGet(urldecode($arrFragments[$i]), urldecode($arrFragments[$i+1]), true);
			}
		}	
		
		// TODO: get controllers from tagged services
		// foreach save array with key=>service-getName() and value=>class
		
		// get service
		
		$controllers = ['page'=>'agoat.controller.page:run', 'posts'=>'agoat.controller.post:run'];
		$controllers = ['page'=>'Agoat\PermalinkBundle\Contao\PageController', 'posts'=>'Agoat\PermalinkBundle\Contao\PostController'];
		
		$stopwatch->start('rendering');
		
		// Set the permalink constant variable
		define('TL_PERMALINK', true);
		
		// TODO: Maybe better instance new class and call the run method directly
		if (($controller = $controllers[$objPermalink->controller]) !== null)
		{
			$controller = new $controller();
			$response = $controller->run($objPermalink->source, $alias);
		
		}
  	    $stopwatch->stop('rendering');

		return $response;


    }

	
    /**
     * Fetch and run the responsible contoller form the database
     *
     * @return Response
     */
    public function rootAction(Request $request)
    {
		// TODO: Logic to redirect to the coresponding language page (from Frontend::getRootPageFromUrl)
		
		// get prefered language
		// get root pages from request->getHost()
		
		
		
        $controller = new FrontendIndex();

        return $controller->run();
    }
}