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
 * Permalink provider factory
 */
class PermalinkProviderFactory
{

    /**
     * @var string
     */
	protected $suffix;
	
    /**
     * @var array
     */
	protected $reservedWords = ['index', 'contao'];
	
    /**
     * @var array
     */
	protected $reservedChars = [';', '?', ':', '@', '=', '&'];

    /**
     * @var array
     */
	protected $unsafeChars = [' ', '"', '<', '>', '#', '%', '{', '}', '[', ']', '|', '\\', '^', '~', '`', '\'', '°'];
	
	
    /**
	 * Constructor
	 */
	public function __construct($suffix)
	{
		$this->suffix = $suffix;
	}
	

    /**
	 * Register a permalink for the given context and source id
	 *
     * @param PermalinkUrl $permalink
     * @param string       $context
	 * @param integer      $source
     */
	protected function registerPermalink (PermalinkUrl $permalink, $context, $source)
	{
		$guid = $permalink->getGuid();
		
		$permalink = \PermalinkModel::findByGuid($guid);

		// The Guid have to be unique
		if (null !== $permalink && $permalink->source != $source)
		{
			throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['permalinkExists'], $guid));
		}
	
		$permalink = \PermalinkModel::findByContextAndSource($context, $source);
	
		if (null === $permalink)
		{
			$permalink = new \PermalinkModel();
			$permalink->guid = $guid;
			$permalink->context = $context;
			$permalink->source = $source;
			
			$permalink->save();
		}
		else if ($permalink->guid != $guid)
		{
			$permalink->guid = $guid;

			$permalink->save();
		}
	}
	
	
    /**
	 * Unregister a permalink for the given context and source id
	 *
     * @param string       $context
	 * @param integer      $source
 	 *
     * @return boolean
    */
	protected function unregisterPermalink ($context, $source)
	{
		$permalink = \PermalinkModel::findByContextAndSource($context, $source);
	
		if (null !== $permalink)
		{
			return ($permalink->delete() > 0);
		}
	}
	
	
    /**
	 * Validates if the path is a valid url
	 *
     * @param string $path
 	 *
     * @return string
 	 *
     * @throws AccessDeniedException
    */
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
