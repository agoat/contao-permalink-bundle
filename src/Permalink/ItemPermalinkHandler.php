<?php

/*
 * This file is part of the permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Permalink;

use Agoat\PermalinkBundle\Model\PermalinkModel;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Input;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\UserModel;
use Symfony\Component\HttpFoundation\Request;


/**
 * Main front end controller.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class ItemPermalinkHandler extends AbstractPermalinkHandler
{
    protected const CONTEXT = 'items';

	/**
     * {@inheritdoc}
     */
	public static function getDcaTable(): string
	{
        return class_exists(NewsModel::class) ? NewsModel::getTable() : 'na';
	}

	/**
     * {@inheritdoc}
     */
	public static function getDefault(): string
	{
		return '{{parent+/}}{{alias}}';
	}

    /**
     * {@inheritdoc}
     */
    public function findPage(int $id, Request $request)
    {
        $objNews = NewsModel::findByPk($id);

        // Throw a 404 error if the event could not be found
        if (null === $objNews)
        {
            throw new PageNotFoundException('Item not found: ' . $request->getUri());
        }

        // Set the event id as get attribute
        Input::setGet('items', $objNews->id, true);

        $objNewsArchive = NewsArchiveModel::FindByPk($objNews->pid);
        $objPage = PageModel::findByPk($objNewsArchive->jumpTo);

        return $objPage;
    }


    /**
     * {@inheritdoc}
     */
	public function generate($source)
	{
		$objNews = NewsModel::findByPk($source);

		if (null === $objNews)
		{
			// throw fatal error;
		}

		$objNews->refresh(); // Fetch current from database (maybe modified from other onsubmit_callbacks)

		$objNewsArchive = NewsArchiveModel::findByPk($objNews->pid);
		$objPage = PageModel::findByPk($objNewsArchive->jumpTo);

		if (null === $objPage)
		{
			// throw fatal error;
		}

		$objPage->refresh(); // Fetch current from database
		$objPage->loadDetails();

		$permalink = new PermalinkUrl();

		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setHost($objPage->domain ?: $this->getHost())
				  ->setPath($this->validatePath($this->generatePathFromPermalink($objNews)))
				  ->setSuffix($this->suffix);

		$this->registerPermalink($permalink, self::CONTEXT, $source);

	}


	/**
     * {@inheritdoc}
     */
	public function remove($source)
	{
		return $this->unregisterPermalink(self::CONTEXT, $source);
	}


	/**
     * {@inheritdoc}
     */
	public function getUrl($source)
	{
		$objNews = NewsModel::findByPk($source);

		if (null === $objNews)
		{
			// throw fatal error;
		}

		$objNewsArchive = NewsArchiveModel::findByPk($objNews->pid);
		$objPage = PageModel::findWithDetails($objNewsArchive->jumpTo);

		if (null === $objPage)
		{
			// throw fatal error;
		}

		$objPermalink = PermalinkModel::findByContextAndSource(self::CONTEXT, $source);

		$permalink = new PermalinkUrl();

		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setGuid((null !== $objPermalink) ? $objPermalink->guid : ($objPage->domain ?: $this->getHost()))
				  ->setSuffix((strpos($permalink->getGuid(), '/')) ? $this->suffix : '');

		return $permalink;
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	protected function generatePathFromPermalink($objNews)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $objNews->permalink, -1, PREG_SPLIT_DELIM_CAPTURE);

		if (count($tags) < 2)
		{
			return $objNews->permalink;
		}

		$buffer = '';

		for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=2)
		{
			$buffer .= $tags[$_rit];
			list($tag,$addition) = explode ('+', $tags[$_rit+1]);

			// Skip empty tags
			if ($tag == '')
			{
				continue;
			}

			// Replace the tag
			switch (strtolower($tag))
			{
				// Alias
				case 'alias':
					$buffer .= StringUtil::generateAlias($objNews->headline) . $addition;
					break;

				// Alias
				case 'author':
					$objUser = UserModel::findByPk($objNews->author);

					if ($objUser)
					{
						$buffer .= StringUtil::generateAlias($objUser->name) . $addition;
					}
					break;

				// Page (alias)
				case 'page':
				case 'parent':
					$objNewsArchive = NewsArchiveModel::findByPk($objNews->pid);
					$objParent = PageModel::findByPk($objNewsArchive->jumpTo);

					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;

				// Date
				case 'date':
					$objNewsArchive = NewsArchiveModel::findByPk($objNews->pid);
					$objPage = PageModel::findWithDetails($objNewsArchive->jumpTo);

					if (!($format = $objPage->dateFormat))
					{
						$format = Config::get('dateFormat');
					}

					$buffer .= StringUtil::generateAlias(date($format, $objNews->date)) . $addition;
					break;

				// Language
				case 'language':
					$objNewsArchive = NewsArchiveModel::findByPk($objNews->pid);
					$objParent = PageModel::findWithDetails($objNewsArchive->jumpTo);

					if ($objParent)
					{
						if (false !== strpos($objParent->permalink, 'language') && 'root' !== $objParent->type)
						{
							break;
						}

						$buffer .= $objParent->rootLanguage . $addition;
					}
					break;

				default:
					throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['unknownInsertTag'], $tag));
			}

		}

		return $buffer;
	}
}
