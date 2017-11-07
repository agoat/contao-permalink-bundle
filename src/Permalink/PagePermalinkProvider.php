<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Permalink;

use Contao\CoreBundle\Exception\AccessDeniedException;


/**
 * Permalink handling for pages
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PagePermalinkProvider extends PermalinkProviderFactory implements PermalinkProviderInterface
{
	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_page';
	}
	
	/**
     * {@inheritdoc}
     */	
	public function generate($context, $source)
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
				  ->setHost($objPage->domain)
				  ->setPath($this->validatePath($this->generatePathFromPermalink($objPage)))
				  ->setSuffix($this->suffix);

		$this->registerPermalink($permalink, $context, $source);
	}

	
	/**
     * {@inheritdoc}
     */	
	public function remove($context, $source)
	{
		return $this->unregisterPermalink($context, $source);
	}

	
	/**
     * {@inheritdoc}
     */	
	public function getUrl($context, $source)
	{
		$objPage = \PageModel::findWithDetails($source);

		if (null === $objPage)
		{
			return new PermalinkUrl();
		}

		$objPermalink = \PermalinkModel::findByContextAndSource($context, $source);
		
		$permalink = new PermalinkUrl();
		
		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setGuid((null !== $objPermalink) ? $objPermalink->guid : $objPage->domain)
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
	protected function generatePathFromPermalink($objPage)
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
					$objPage = \PageModel::findWithDetails($objPage->id);
					
					if ($objPage)
					{
						$buffer .= $objPage->rootLanguage . $addition;
					}
					break;
				
				default:
					throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['unknownInsertTag'], $tag)); 
			}
			
		}
		
		return $buffer;
	}
}