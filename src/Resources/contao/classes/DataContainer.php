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

use Contao\CoreBundle\Exception\AccessDeniedException;


class DataContainer extends \Contao\Controller
{
	
	/**
	 * Add extra css and js to the backend template
	 */
	public function onLoadDataContainer ($strTable)
	{
		if (TL_MODE == 'FE')
		{
			return;
		}

		// Remove the url settings (and add default permalink structure)
		if ($strTable == 'tl_settings')
		{
			$this->addPermalinkSettings($strTable);
			return;
		}

		if (!\System::getContainer()->get('permalink.generator')->supportsTable($strTable))
		{
			return;
		}
		
		$this->addPermalinkField($strTable);
	}


	/**
	 * Add extra css and js to the backend template
	 */
	public function generatePermalink ($value, $dc)
	{
		if (empty($value))
		{
			$value = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['default'];
		}
		
		return $value;
		
	}
	

	/**
	 * Add extra css and js to the backend template
	 */
	public function generatePermalink2 ($dc)
	{
		$generator = \System::getContainer()->get('permalink.generator');
		$context = $generator->getContextForTable($dc->table);
	dump($dc);	
		try
		{
			$alias = $generator->createAlias($dc);
			$host = $generator->getHost($dc);
			
			//$generator->generatePermalink($context, $dc->id);
		}
		catch (ResponseException $e)
		{
			throw $e;
		}
		catch (\Exception $e)
		{
			 /** @var AttributeBagInterface $objSessionBag */
			$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');
			
			// Save permalink error to session instead of throwing an exception (will be handled later in the permlinkWizard)
			$objSessionBag->set('permalink_error', $e->getMessage()); 
			return;
		}

		$guid = $host . '/' . $alias;
		
		$objGuid = \PermalinkModel::findByGuid($guid);

		// The Guid have to be unique
		if (null !== $objGuid && $objGuid->source != $dc->id)
		{
			 /** @var AttributeBagInterface $objSessionBag */
			$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');
			$objSessionBag->set('permalink_error', sprintf($GLOBALS['TL_LANG']['ERR']['permalinkExists'], $guid)); 
			
			return;
		}
		
		if ('root' !== $dc->activeRecord->type)
		{
			$this->savePermalink($guid, $dc->id, $dc->table);
			$this->saveAlias($alias, $dc->id, $dc->table);
		}
		
		// TODO: Check for subpages and recreate the permalinks
		// or
		// TODO: Check for tables with permalink context and look for records where this is the parent
		

	}
	

	/**
	 * Add extra css and js to the backend template
	 */
	protected function savePermalink ($guid, $intId, $strTable)
	{
		$context = \System::getContainer()->get('permalink.generator')->getContextForTable($strTable);
dump($context);		
		$objPermalink = \PermalinkModel::findByContextAndSource($context, $intId);
dump($objPermalink);	
		if (null === $objPermalink)
		{
			$objPermalink = new \PermalinkModel();
			$objPermalink->guid = $guid;
			$objPermalink->context = $context;
			$objPermalink->source = $intId;
			
			$objPermalink->save();
		}
		else if ($objPermalink->guid != $guid)
		{
			$objPermalink->guid = $guid;

			$objPermalink->save();
		}
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function saveAlias ($alias, $intId, $strTable)
	{
		$db = \Database::getInstance();
		
		$db->execute("UPDATE $strTable SET alias='$alias' WHERE id='$intId'");
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function replacePlaceholderTags ($value, $dc)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		dump($tags);
		dump($dc->activeRecord->title);
		
		if (count($tags) < 2)
		{
			return $value;
		}
		
		$buffer = '';
		
		for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=2)
		{
			$buffer .= $tags[$_rit];
			$tag = $tags[$_rit+1];
		dump($tag);			
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
					$buffer .= \StringUtil::generateAlias($dc->activeRecord->title);
					break;
			
				// Parent (alias)
				case 'parent':

				// Language
				case 'language':

				// Language
				case 'language':

			}
		}
		
dump($buffer);		
		return $buffer;
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function addPermalinkField ($strTable)
	{
		$context = \System::getContainer()->get('permalink.generator')->getContextForTable($strTable);
		
		$GLOBALS['TL_DCA'][$strTable]['config']['onload_callback'][] = array('Agoat\\Permalink\\DataContainer', 'modifyPalette');
		$GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = array('Agoat\\Permalink\\DataContainer', 'generatePermalink2');
		$GLOBALS['TL_DCA'][$strTable]['fields']['permalink'] = array
		(
			'label'			=> &$GLOBALS['TL_LANG'][$strTable]['permalink'],
			'explanation'	=> $context,
			'default'		=> \Config::get($context.'Permalink'),
			'exclude'		=> true,
			'search'		=> true,
			'inputType'		=> 'permalinkWizard',
			'eval'			=> array('mandatory'=>false, 'helpwizard'=>true, 'doNotCopy'=>true, 'maxlength'=>128, 'tl_class'=>'clr'),
			'save_callback' => array
			(
				array('Agoat\\Permalink\\DataContainer', 'generatePermalink')
			),
			'sql'			=> "varchar(128) COLLATE utf8_bin NOT NULL default ''"
		);
		
		$GLOBALS['TL_LANG'][$strTable]['permalink'] = $GLOBALS['TL_LANG']['DCA']['permalink'];
		$GLOBALS['TL_LANG'][$strTable]['permalink_legend'] = $GLOBALS['TL_LANG']['DCA']['permalink_legend'];
	}

	
	/**
	 * Add extra css and js to the backend template
	 */
	public function modifyPalette ($dc)
	{
		$pattern = ['/,alias/', '/{title_legend}.*?;/'];
		$replace = ['', '$0{permalink_legend},permalink;'];
		
		$palettes = array_diff(array_keys($GLOBALS['TL_DCA'][$dc->table]['palettes']), array('__selector__'));
	
	
		foreach ($palettes as $palette)
		{
			$GLOBALS['TL_DCA'][$dc->table]['palettes'][$palette] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA'][$dc->table]['palettes'][$palette]);
		}
		
	
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	public function addPermalinkSettings ($strTable)
	{
		$providers = (array) \System::getContainer()->get('permalink.generator')->getProviders();

		foreach($providers as $context=>$provider)
		{
			$GLOBALS['TL_DCA']['tl_settings']['fields'][$context.'Permalink'] = array
			(
				'label'			=> &$GLOBALS['TL_LANG']['tl_settings'][$context.'_permalink'],
				//'default'		=> \System::getContainer()->getParameter('permalink.default.page'),
				'inputType'		=> 'text',
				'eval'			=> array('tl_class'=>'w50'),
			);
			
			$palette .= ','.$context.'Permalink';
		}
		
		$pattern = ['/,useAutoItem/', '/,folderUrl/', '/({frontend_legend}.*?);/'];
		$replace = ['', '', '$1'.$palette.';'];
		
		$GLOBALS['TL_DCA'][$strTable]['palettes']['default'] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA'][$strTable]['palettes']['default']);
		
	}

	
}
