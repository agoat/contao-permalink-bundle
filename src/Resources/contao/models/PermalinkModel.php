<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


class PermalinkModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_permalink';

	
	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findByGuid($guid, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.guid=?");
		$arrValues = array($guid);

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findByControllerAndSource($strController, $intSource, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.controller=? AND $t.source=?");
		$arrValues = array($strController, $intSource);

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findByGuids($arrGuids, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.guid IN('" . implode("','", array_filter($arrGuids)) . "')");

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = \Database::getInstance()->findInSet("$t.guid", $arrGuids);
		}

		return static::findOneBy($arrColumns, null, $arrOptions);
	}

	
}