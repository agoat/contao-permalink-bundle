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
	protected function getInheritDetails($activeRecord)
	{
		return \PageModel::findWithDetails($activeRecord->id);
	}


	/**
     * {@inheritdoc}
     */	
	public function getHost($activeRecord)
	{
		return \PageModel::findWithDetails($activeRecord->id)->domain;
	}


	/**
     * {@inheritdoc}
     */	
	public function getSchema($activeRecord)
	{
		return \PageModel::findWithDetails($activeRecord->id)->rootUseSSL ? 'https://' : 'http://';
	}


	/**
     * {@inheritdoc}
     */	
	public function getLanguage($id)
	{
		return \PageModel::findWithDetails($id)->rootLanguage;
	}


	/**
     * {@inheritdoc}
     */	
	public function createAlias($activeRecord)
	{
		$objPage = \PageModel::findByPk($activeRecord->id);
		
		$alias = $this->replaceInsertTags($activeRecord);
		
		return $alias;
	}

	
	/**
     * {@inheritdoc}
     */	
	public function generate($id)
	{
		$objPage = \PageModel::findByPk($id);
	dump($objPage);	

		$objPage2 = \PageModel::findByPk($id);
	dump($objPage2);	


		$path = $this->replaceInsertTags($objPage->permalink, $objPage);

		
		
		
		// path (replaced)
		// host
		
		// save to permalink table
		// if path not 'index' save host.path
		// else save only host
		
		// save to page table
		// save path
		
		// check for subpages and (re)save new premalink
		
		return $path;
	}

	
	/**
     * {@inheritdoc}
     */	
	public function getAbsoluteUrl($source)
	{

		$objPage = \PageModel::findWithDetails($source);
		
		$objPermalink = \PermalinkModel::findByContextAndSource('page', $source);

		$schema = $objPage->rootUseSSL ? 'https://' : 'http://';
		$guid = $objPermalink->guid;
		$suffix = $this->suffix;
		
		return $schema . $guid . $suffix;
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	protected function replaceInsertTags($activeRecord)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $activeRecord->permalink, -1, PREG_SPLIT_DELIM_CAPTURE);
	
		if (count($tags) < 2)
		{
			return $activeRecord->permalink;
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
					return 'index';
					break;
			
				// Alias
				case 'alias':
					$buffer .= \StringUtil::generateAlias($activeRecord->title) . $addition;
					break;
			
				// Parent (alias)
				case 'parent':
					$objParent = \PageModel::findByPk($activeRecord->pid);

					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;
					
				// Language
				case 'language':
					$objPage = \PageModel::findWithDetails($activeRecord->id);
					
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