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
 * Main front end controller.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class EventPermalinkProvider extends PermalinkProviderFactory implements PermalinkProviderInterface
{

	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_calendar_events';
	}


	/**
     * {@inheritdoc}
     */	
	public function generate($context, $source)
	{
		$objEvent = \CalendarEventsModel::findByPk($source);

		if (null === $objEvent)
		{
			// throw fatal error;
		}

		$objCalender = \CalendarModel::findByPk($objEvent->pid);
		$objPage = \PageModel::findWithDetails($objCalender->jumpTo);

		if (null === $objPage)
		{
			// throw fatal error;
		}

		$permalink = new PermalinkUrl();
		
		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setHost($objPage->domain)
				  ->setPath($this->validatePath($this->replaceInsertTags($objEvent)))
				  ->setSuffix($this->suffix)
				  ->setContext($context)
				  ->setSource($source);

		$this->registerPermalink($permalink, $context, $source);
		
	}

	
	/**
     * {@inheritdoc}
     */	
	public function getUrl($context, $source)
	{
		$objEvent = \CalendarEventsModel::findByPk($source);

		if (null === $objEvent)
		{
			// throw fatal error;
		}

		$objCalender = \CalendarModel::findByPk($objEvent->pid);
		$objPage = \PageModel::findWithDetails($objCalender->jumpTo);

		if (null === $objPage)
		{
			// throw fatal error;
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
	protected function replaceInsertTags($objEvent)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $objEvent->permalink, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		if (count($tags) < 2)
		{
			return $objEvent->permalink;
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
					$buffer .= \StringUtil::generateAlias($objEvent->title) . $addition;
					break;
			
				// Alias
				case 'author':
					$objUser = \UserModel::findByPk($objEvent->author);
					
					if ($objUser)
					{
						$buffer .= \StringUtil::generateAlias($objUser->name) . $addition;
					}
					break;
			
				// Parent (alias)
				case 'parent':
					$objCalender = \CalendarModel::findByPk($objEvent->pid);
					$objParent = \PageModel::findByPk($objCalender->jumpTo);
				
					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;
					
				// Date
				case 'date':
					$objCalender = \CalendarModel::findByPk($objEvent->pid);
					$objPage = \PageModel::findWithDetails($objCalender->jumpTo);
	
					if (!($format = $objPage->dateFormat))
					{
						$format = \Config::get('dateFormat');
					}
				
					$buffer .= \StringUtil::generateAlias(date($format, $objEvent->startDate)) . $addition;
					break;
			
				// Language
				case 'language':
				
				
				default:
					throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['unknownInsertTag'], $tag)); 
			}
			
		}
		
		return $buffer;
	}
}