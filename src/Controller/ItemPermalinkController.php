<?php

/**
 * Contao Open Source CMS - Permalink Extension
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Controller;

use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Main front end controller.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class ItemPermalinkController implements PermalinkControllerInterface
{

	/**
     * {@inheritdoc}
     */
	public function getDcaTable()
	{
		return 'tl_news';
	}


	/**
	 * Find the corresponding page and run the FrontendIndex controller
	 *
	 * @param integer $source
	 * @param Request $request
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	public function run($source, Request $request)
	{
		$objNews = \NewsModel::findByPk($source);

		// Throw a 404 error if the event could not be found
		if (null === $objNews)
		{
			throw new PageNotFoundException('Item not found: ' . $request->getUri());
		}

		// Set the event id as get attribute
		\Input::setGet('items', $objNews->id, true);

		$objNewsArchive = \NewsArchiveModel::FindByPk($objNews->pid);
		$objPage = \PageModel::findByPk($objNewsArchive->jumpTo);

		// Render the corresponding page from the calender setting
		$frontendIndex = new FrontendIndex();
		return $frontendIndex->renderPage($objPage);
	}
}
