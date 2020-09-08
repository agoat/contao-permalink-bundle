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

use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Page controller
 */
class PagePermalinkController implements PermalinkControllerInterface
{

	/**
     * {@inheritdoc}
     */
	public function getDcaTable()
	{
		return 'tl_page';
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
		$objPage = \PageModel::findPublishedById($source);

		// Legacy handling (if there is a subpage with the alias existing)
		if (null !== $request->attributes->get('alias') && null !== ($objSubPage = \PageModel::findPublishedByIdOrAlias($request->attributes->get('alias'))))
		{
			$objPage = $objSubPage;
		}

		// Throw a 404 error if the page could not be found
		if (null === $objPage)
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri());
		}

		$frontendIndex = new FrontendIndex();
		return $frontendIndex->renderPage($objPage);
	}
}
