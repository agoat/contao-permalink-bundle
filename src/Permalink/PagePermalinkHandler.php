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

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\FrontendIndex;
use Symfony\Component\HttpFoundation\Request;


/**
 * Permalink provider for pages
 */
class PagePermalinkHandler extends AbstractPermalinkHandler
{
    protected const CONTEXT = 'page';


	/**
     * {@inheritdoc}
     */
	public static function getDcaTable(): string
	{
		return \PageModel::getTable();
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
    public function getPage($source, Request $request)
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

        return $objPage;
    }

    /**
     * {@inheritdoc}
     */
	public function generate($source)
	{
		$objPage = \PageModel::findByPk($source);

		if (null === $objPage)
		{
			// throw fatal error;
		}

		if ('root' == $objPage->type) // Don't save permalink for root pages
		{
			return;
		}

		$objPage->refresh(); // Fetch current from database (maybe modified from other onsubmit_callbacks)
		$objPage->loadDetails();

		$permalink = new PermalinkUrl();

		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setHost($objPage->domain ?: $this->getHost())
				  ->setPath($this->validatePath($this->resolvePattern($objPage)))
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
		$objPage = \PageModel::findWithDetails($source);

		if (null === $objPage)
		{
			return new PermalinkUrl();
		}

		$objPermalink = \PermalinkModel::findByContextAndSource(self::CONTEXT, $source);

		$permalink = new PermalinkUrl();

		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setGuid((null !== $objPermalink) ? $objPermalink->guid : ($objPage->domain ?: $this->getHost()))
				  ->setSuffix((strpos($permalink->getGuid(), '/')) ? $this->suffix : '');

		return $permalink;
	}


	/**
	 * Resolve pattern to strings
	 *
	 * @param \PageModel $objPage
	 *
	 * @return String
	 *
	 * @throws AccessDeniedException
	 */
	protected function resolvePattern($objPage)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $objPage->permalink, -1, PREG_SPLIT_DELIM_CAPTURE);

		if (count($tags) < 2)
		{
			return $objPage->permalink;
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
				// Root
				case 'index':
					return '';
					break;

				// Alias
				case 'alias':
					$buffer .= \StringUtil::generateAlias($objPage->title) . $addition;
					break;

				// Parent (alias)
				case 'parent':
					$objParent = \PageModel::findByPk($objPage->pid);

					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;

				// Language
				case 'language':
					$objParent = \PageModel::findWithDetails($objPage->pid);

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
