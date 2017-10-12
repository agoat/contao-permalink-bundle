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
	public function getHost($activeRecord)
	{
		$objCalender = \CalendarModel::findByPk($activeRecord->pid);

		return \PageModel::findWithDetails($objCalender->jumpTo)->domain;
	}


	/**
     * {@inheritdoc}
     */	
	public function getSchema($id)
	{
		$objEvents = \CalendarEventsModel::findByPk($id);
		$objCalender = \CalendarModel::findByPk($objEvents->pid);

		return \PageModel::findWithDetails($objCalender->jumpTo)->rootUseSSL ? 'https://' : 'http://';
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
	public function getParentAlias($id)
	{
		return \PageModel::findWithDetails($id)->parentAlias;
	}

	/**
     * {@inheritdoc}
     */	
	protected function getInheritDetails($activeRecord)
	{
		$objCalendar = \CalendarModel::findByPk($activeRecord->pid);

		return \PageModel::findWithDetails($objCalendar->jumpTo);
	}

	
	/**
     * {@inheritdoc}
     */	
	public function createAlias($activeRecord)
	{
	dump($activeRecord);	
		$alias = $this->replaceInsertTags($activeRecord);
	dump($alias);	
		return $alias;
	}


	/**
     * {@inheritdoc}
     */	
	public function getAbsoluteUrl($source)
	{
		$objEvent = \CalendarEventsModel::findByPk($source);
		$objCalender = \CalendarModel::findByPk($objEvent->pid);

		$objPage = \PageModel::findWithDetails($objCalender->jumpTo);
	dump($source);	
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
				// Alias
				case 'alias':
					$buffer .= \StringUtil::generateAlias($activeRecord->title) . $addition;
					break;
			
				// Alias
				case 'author':
					$objUser = \UserModel::findByPk($activeRecord->author);
					
					if ($objUser)
					{
						$buffer .= \StringUtil::generateAlias($objUser->name) . $addition;
					}
					break;
			
				// Parent (alias)
				case 'parent':
					$objCalender = \CalendarModel::findByPk($activeRecord->pid);
					$objParent = \PageModel::findByPk($objCalender->jumpTo);
				
					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;
					
				// Date
				case 'date':
					$objCalender = \CalendarModel::findByPk($activeRecord->pid);
					$objPage = \PageModel::findWithDetails($objCalender->jumpTo);
	
					if (!($format = $objPage->dateFormat))
					{
						$format = \Config::get('dateFormat');
					}
				
					$buffer .= \StringUtil::generateAlias(date($format, $activeRecord->startDate)) . $addition;
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