<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Response;


/**
 * Main front end controller.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PostController extends \Frontend
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		// Load the user object before calling the parent constructor
		$this->import('FrontendUser', 'User');
		parent::__construct();

		// Check whether a user is logged in
		define('BE_USER_LOGGED_IN', $this->getLoginStatus('BE_USER_AUTH'));
		define('FE_USER_LOGGED_IN', $this->getLoginStatus('FE_USER_AUTH'));
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	public function run($source)
	{
        $objPost = \PostsModel::findByPk($source);

		if (null === $objPost)
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri());
		}
	
		// Set the posts id as GET value
		\Input::SetGet('posts', $source);
		
		return $this->renderPost($objPost);
	}

	/**
	 * Render a page
	 *
	 * @param Model\Collection|PageModel[]|PageModel $pageModel
	 *
	 * @return Response
	 *
	 * @throws \LogicException
	 * @throws PageNotFoundException
	 * @throws AccessDeniedException
	 */
	public function renderPost($postModel)
	{
		global $objPage;

		// TODO: PostModel->getWithDetails / loadDetails >> getArchivDetails + Layout

		$objArchive = \ArchiveModel::findByPk($postModel->pid);
		
		$objPage = \PageModel::findWithDetails($objArchive->pid);
		
		// Set the admin e-mail address
		if ($objPage->adminEmail != '')
		{
			list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \StringUtil::splitFriendlyEmail($objPage->adminEmail);
		}
		else
		{
			list($GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) = \StringUtil::splitFriendlyEmail(\Config::get('adminEmail'));
		}

		// Exit if the root page has not been published (see #2425)
		// Do not try to load the 404 page, it can cause an infinite loop!
		if (!BE_USER_LOGGED_IN && !$objPage->rootIsPublic)
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}

		// Check wether the language matches the root page language
		if (\Config::get('addLanguageToUrl') && isset($_GET['language']) && \Input::get('language') != $objPage->rootLanguage)
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}

		// Check whether there are domain name restrictions
		if ($objPage->domain != '')
		{
			// Load an error 404 page object
			if ($objPage->domain != \Environment::get('host'))
			{
				$this->User->authenticate();
				$this->log('Page ID "' . $objPage->id . '" was requested via "' . \Environment::get('host') . '" but can only be accessed via "' . $objPage->domain . '" (' . \Environment::get('base') . \Environment::get('request') . ')', __METHOD__, TL_ERROR);

				throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
			}
		}

		// Authenticate the user
		if (!$this->User->authenticate() && $objPage->protected)
		{
			throw new AccessDeniedException('Access denied: ' . \Environment::get('uri'));
		}

		// Check the user groups if the page is protected
		if ($objPage->protected)
		{
			$arrGroups = $objPage->groups; // required for empty()

			if (!is_array($arrGroups) || empty($arrGroups) || !count(array_intersect($arrGroups, $this->User->groups)))
			{
				$this->log('Page ID "' . $objPage->id . '" can only be accessed by groups "' . implode(', ', (array) $objPage->groups) . '" (current user groups: ' . implode(', ', $this->User->groups) . ')', __METHOD__, TL_ERROR);
				throw new AccessDeniedException('Access denied: ' . \Environment::get('uri'));
			}
		}

		// Backup some globals (see #7659)
		$arrHead = $GLOBALS['TL_HEAD'];
		$arrBody = $GLOBALS['TL_BODY'];
		$arrMootools = $GLOBALS['TL_MOOTOOLS'];
		$arrJquery = $GLOBALS['TL_JQUERY'];

		// Render the post as regular page
		try
		{
			$objHandler = new $GLOBALS['TL_PTY']['regular']();

			/** @var PageRegular $objHandler */
			return $objHandler->getResponse($objPage, true);
		}
		
		// Render the error page (see #5570)
		catch (\UnusedArgumentsException $e)
		{
			// Restore the globals (see #7659)
			$GLOBALS['TL_HEAD'] = $arrHead;
			$GLOBALS['TL_BODY'] = $arrBody;
			$GLOBALS['TL_MOOTOOLS'] = $arrMootools;
			$GLOBALS['TL_JQUERY'] = $arrJquery;

			/** @var PageError404 $objHandler */
			$objHandler = new $GLOBALS['TL_PTY']['error_404']();

			return $objHandler->getResponse();
		}
	}
}