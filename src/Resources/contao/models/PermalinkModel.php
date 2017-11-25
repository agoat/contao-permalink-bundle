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

namespace Contao;


/**
 * Reads and writes permalink
 */
class PermalinkModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_permalink';


	/**
	 * Find a permalink by its guid
	 *
	 * @param string $strGuid    The guid (host/path)
	 * @param array  $arrOptions An optional options array
	 *
	 * @return Model|PermalinkModel|null A model or null if there are is no permalink for the given guid
	 */
	public static function findByGuid($strGuid, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.guid=?");
		$arrValues = array($strGuid);

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find a permalink by its context and source
	 *
	 * @param string  $strContext The context
	 * @param integer $intSource  The source id
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model|PermalinkModel|null A model or null if there are is no permalink for the given context and source
	 */
	public static function findByContextAndSource($strContext, $intSource, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.context=? AND $t.source=?");
		$arrValues = array($strContext, $intSource);

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}
}
