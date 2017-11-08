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
use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\NoRootPageFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


/**
 * Handles the Contao frontend routes.
 *
 * @author Arne Stappen <https://github.com/agoat>
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class GuidController extends Controller
{
	/**
	 * Fetch and run the responsible contoller form the database
	 *
	 * @return Response
	 */
	public function frontendAction($path, Request $request)
	{
		$stopwatch = $this->get('debug.stopwatch');
		$stopwatch->start('routing');

		// First try to find an url entry directly
		$objPermalink = \PermalinkModel::findByGuid($request->getHost() . '/' . $path);

		// Then try to find a parent url entry
		while (null === $objPermalink && strpos($path, '/') !== false)
		{
			$arrFragments[] = basename($path);
			$path = dirname($path);

			$objPermalink = \PermalinkModel::findByGuid($request->getHost() . '/' . $path);
		}
	
		if (null === $objPermalink)
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri());
			// Try to find a page the old way (legacy support)
			$controller = new FrontendIndex();
			return $controller->run();
		}

		// Save the fragments for modules
		if (!empty($arrFragments))
		{
			$arrFragments = array_reverse($arrFragments);
		
			$legacy = in_array($arrFragments[0], $GLOBALS['TL_AUTO_ITEM']);
			
			// Save fragments as get paramters
			foreach ($arrFragments as $key=>$value)
			{
				\Input::setGet($key, $value, !$legacy);
			}
	
			// Save as key value pairs (legacy support)
			if ($legacy)
			{
				// Add the fragments to the $_GET array (legacy support)
				for ($i=0, $c=count($arrFragments); $i<$c; $i+=2)
				{
					// Skip key value pairs if the key is empty (see #4702)
					if ($arrFragments[$i] == '')
					{
						continue;
					}
					
					// Skip duplicate parameter (duplicate content) (see #4277)
					if (isset($_GET[$arrFragments[$i]]))
					{
						continue;
					}
					
					\Input::setGet(urldecode($arrFragments[$i]), urldecode($arrFragments[$i+1]), true);
				}
			}	
		}	

		$stopwatch->stop('routing');

		$controllerChain = $this->get('contao.controller.chain');
	
		if (($controller = $controllerChain->getController($objPermalink->context)) !== null)
		{
			$controller = new $controller();
			
			$stopwatch->start('rendering');
			$response = $controller->run($objPermalink->source, $request);
			$stopwatch->stop('rendering');
		}
		else
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri());
		}
	
		return $response;
	}

	
	/**
	 * Fetch a matching lanugage page and redirect
	 *
	 * @return Response
	 */
	public function rootAction(Request $request)
	{
		// First try to find an url entry directly
		$objPermalink = \PermalinkModel::findByGuid($request->getHost());

		// Then try to find a root page and redirect to the first regular page
		if (null === $objPermalink)
		{
			// if (redirectempty) Contao config acess ????????
			if (Config::get('doNotRedirectEmpty'))
			{
				$objRootPage = \PageModel::findBy(['type=?', 'dns=?', 'fallback=?'], ['root', $request->getHost(), 1], ['limit'=>1]);
				
				if (null === $objRootPage)
				{
					throw new NoRootPageFoundException('No rootpage found');
				}
				
				$source = $objRootPage->id;
			}
			else
			{
				$objRootPages = \PageModel::findBy(['type=?', 'dns=?'], ['root', $request->getHost()], ['order'=>'fallback DESC']);
				
				if (null === $objRootPages)
				{
					throw new NoRootPageFoundException('No rootpage found');
				}
				
				$availableLanguages = $objRootPages->fetchEach('language');
			
				$language = $request->getPreferredLanguage($availableLanguages);
			
				$source = array_flip($availableLanguages)[$language];
			}
	
			$objPage = \PageModel::findFirstPublishedByPid($source);
			
			if (null === $objPage)
			{
				throw new NoRootPageFoundException('No regular page found');
			}
			
			return $this->redirectToRoute('contao_guid_frontend', array('path' => $objPage->alias));
		}
	
		$controllerChain = $this->get('contao.controller.chain');
	
		if (($controller = $controllerChain->getController($objPermalink->context)) !== null)
		{
			$controller = new $controller();
			
			$response = $controller->run($objPermalink->source, $request);
		}
		else
		{
			throw new NoRootPageFoundException('No rootpage found: ' . $request->getUri());
		}
	
		return $response;
	}
}