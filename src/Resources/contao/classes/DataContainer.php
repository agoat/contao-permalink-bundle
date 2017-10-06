<?php
 
 /**
 * Contao Open Source CMS - Permalink extension
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   permalink
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\Permalink;


class DataContainer extends \Contao\Controller
{
	
	protected $pattern = ['/,alias/', '/{title_legend}.*?;/', '/,useAutoItem/', '/,folderUrl/'];
	protected $replace = ['', '$0{permalink_legend},permalink;', '', ''];

	/**
	 * Add extra css and js to the backend template
	 */
	public function onLoadDataContainer ($strTable)
	{
		if (TL_MODE == 'FE')
		{
			return;
		}
				
		// TODO: Add check for registered permalink services/controller
		$GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = ['Agoat\\Permalink\\DataContainer', 'onSubmitDataContainer'];
		
		// Replace the alias field with the permalink widget
		if (strpos($GLOBALS['TL_DCA'][$strTable]['palettes']['default'], 'alias'))
		{
			$this->addPermalinkField($strTable);
			//$this->clearAliasField($strTable); // Should be done by a compiler pass
		}
		
		// Remove the url settings (and add default permalink structure)
		if ($strTable == 'tl_settings')
		{
			$GLOBALS['TL_DCA'][$strTable]['palettes']['default'] = str_replace([',useAutoItem', ',folderUrl'], '', $GLOBALS['TL_DCA'][$strTable]['palettes']['default']);
		}
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	public function onSubmitDataContainer ($dc)
	{
		if (TL_MODE == 'FE')
		{
			return;
		}
				
		dump($dc);
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	public function modifyPalette ($dc)
	{
		$palettes = array_diff(array_keys($GLOBALS['TL_DCA'][$dc->table]['palettes']), array('__selector__'));
	
		foreach ($palettes as $palette)
		{
			$GLOBALS['TL_DCA'][$dc->table]['palettes'][$palette] = preg_replace($this->pattern, $this->replace, $GLOBALS['TL_DCA'][$dc->table]['palettes'][$palette]);
		}
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	public function generatePermalink ($value, $dc)
	{
		// TODO: create permalink with placeholder logic
		$permalink = $value;

		if ($permalink == $dc->activeRecord->alias)
		{
			return $value;
		}

		// TODO: get controller from tagged services
		$context = str_replace('tl_', '', $dc->table);

		$guid = \Environment::get('host') . '/' . $permalink;
		
		$objPermalink = \PermalinkModel::findByControllerAndSource($context, $dc->id);
		
		// TODO: Check if permalink already exists
		
		if (null === $objPermalink)
		{
			$objPermalink = new \PermalinkModel();
			$objPermalink->guid = $guid;
			$objPermalink->controller = $context;
			$objPermalink->source = $dc->id;
			
			$objPermalink->save();
		}
		else if ($objPermalink->guid != $guid)
		{
			$objPermalink->guid = $guid;

			$objPermalink->save();
		}

		$this->saveAliasValue($permalink, $dc->id, $dc->table);
		
		return $value;
	}
	

	/**
	 * Add extra css and js to the backend template
	 */
	protected function saveAliasValue ($alias, $intId, $strTable)
	{
		$db = \Database::getInstance();
		
		$db->execute("UPDATE $strTable SET alias='$alias' WHERE id='$intId'");
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function clearAliasField ($strTable)
	{
		$db = \Database::getInstance();
		
		if ($db->fieldExists('alias', $strTable))
		{
			$db->execute("UPDATE $strTable SET alias=''");
		}
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function addPermalinkField ($strTable)
	{
		$GLOBALS['TL_DCA'][$strTable]['config']['onload_callback'][] = array('Agoat\\Permalink\\DataContainer', 'modifyPalette');

		$GLOBALS['TL_DCA'][$strTable]['fields']['permalink'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['permalink'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('doNotCopy'=>true, 'maxlength'=>128, 'tl_class'=>'w50 clr'),
			'save_callback' => array
			(
				array('Agoat\\Permalink\\DataContainer', 'generatePermalink')
			),
			'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
		);
	}


}
