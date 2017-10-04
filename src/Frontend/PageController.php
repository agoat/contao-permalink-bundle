<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Frontend;

use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Main front end controller.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PageController implements ControllerInterface
{

	/**
     * {@inheritdoc}
     */	
	public function getTable()
	{
		return 'tl_page';
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	public function run($source, $alias, Request $request)
	{
		$objPage = \PageModel::findByPk($source);

		// Legacy handling (look if there is a Page alias)
		if (($objSubPage = \PageModel::findOneByAlias($request->attributes->get('alias'))) !== null)
		{
			$objPage = $objSubPage;
		}

		// Throw a 404 error if the page could not be found
		if (null === $objPage)
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri()); //$request is null (requestStack?? )
		}
		
		$frontendIndex = new FrontendIndex();
		return $frontendIndex->renderPage($objPage);
	}
}