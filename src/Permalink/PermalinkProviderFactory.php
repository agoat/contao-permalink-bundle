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
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PermalinkProviderFactory
{
	protected $suffix;
	
	protected $reservedWords = ['index', 'contao'];
	
	protected $reservedChars = [';', '?', ':', '@', '=', '&'];

	protected $unsafeChars = [' ', '"', '<', '>', '#', '%', '{', '}', '[', ']', '|', '\\', '^', '~', '`', '\'', '°'];
	
	
	public function __construct($suffix)
	{
		$this->suffix = $suffix;
	}
	
	protected function registerPermalink (PermalinkUrl $permalink, $context, $source)
	{
		$guid = $permalink->getGuid();
	//	$context = $permalink->getContext();
	//	$source = $permalink->getSource();
		
		$objGuid = \PermalinkModel::findByGuid($guid);

		// The Guid have to be unique
		if (null !== $objGuid && $objGuid->source != $source)
		{
			throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['permalinkExists'], $guid));
		}
	
		$objPermalink = \PermalinkModel::findByContextAndSource($context, $source);
	
		if (null === $objPermalink)
		{
			$objPermalink = new \PermalinkModel();
			$objPermalink->guid = $guid;
			$objPermalink->context = $context;
			$objPermalink->source = $source;
			
			$objPermalink->save();
		}
		else if ($objPermalink->guid != $guid)
		{
			$objPermalink->guid = $guid;

			$objPermalink->save();
		}

	}
	
	
	protected function unregisterPermalink ($context, $source)
	{
		$objPermalink = \PermalinkModel::findByContextAndSource($context, $source);
	
		if (null !== $objPermalink)
		{
			return ($objPermalink->delete() > 0);
		}
	}
	
	
	protected function validatePath (string $path)
	{
		$path = html_entity_decode($path);
		
		foreach ($this->reservedWords as $reserved)
		{
			if ($path == $reserved)
			{
				throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['permalinkReservedWord'], htmlentities($path)));
			}
		}
	
		foreach ($this->reservedChars as $reserved)
		{
			if (false !== stripos($path, $reserved))
			{
				throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['permalinkReservedChars'], htmlentities($path), $reserved));
			}
			
		}

		foreach ($this->unsafeChars as $unsafe)
		{
			if (false !== stripos($path, $unsafe))
			{
				throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['permalinkUnsafeChars'], htmlentities($path), $unsafe));
			}
			
		}

		return $path;
	}
}