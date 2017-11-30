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


/**
 * Permalink provider for events
 */
class EventsPermalinkProvider extends PermalinkProviderFactory implements PermalinkProviderInterface
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

		$objEvent->refresh(); // Fetch current from database (maybe modified from other onsubmit_callbacks)

		$objCalender = \CalendarModel::findByPk($objEvent->pid);
		$objPage = \PageModel::findByPk($objCalender->jumpTo);

		if (null === $objPage)
		{
			// throw fatal error;
		}

		$objPage->refresh(); // Fetch current from database
		$objPage->loadDetails();
		
		$permalink = new PermalinkUrl();
		
		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setHost($objPage->domain ?: $this->getHost())
				  ->setPath($this->validatePath($this->resolvePattern($objEvent)))
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
				  ->setGuid((null !== $objPermalink) ? $objPermalink->guid : ($objPage->domain ?: $this->getHost()))
				  ->setSuffix((strpos($permalink->getGuid(), '/')) ? $this->suffix : '');

		return $permalink;
	}


	/**
	 * Resolve pattern to strings
	 *
	 * @param \PostsModel $objPost
	 *
	 * @return String
	 *
	 * @throws AccessDeniedException
	 */
	protected function resolvePattern($objEvent)
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
			
				// Page (alias)
				case 'page':
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
			
				// Time
				case 'time':
					$objCalender = \CalendarModel::findByPk($objEvent->pid);
					$objPage = \PageModel::findWithDetails($objCalender->jumpTo);
	
					if (!($format = $objPage->timeFormat))
					{
						$format = \Config::get('timeFormat');
					}
				
					$buffer .= \StringUtil::generateAlias(str_replace(':', '-', date($format, $objEvent->startTime))) . $addition;
					break;
			
				// Language
				case 'language':
					$objCalender = \CalendarModel::findByPk($objEvent->pid);
					$objParent = \PageModel::findWithDetails($objCalender->jumpTo);
					
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
