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

		if (\System::getContainer()->get('contao.permalink.generator')->supportsTable($strTable))
		{
			$this->addPermalinkField($strTable);
		}
	}


	/**
	 * Add extra css and js to the backend template
	 */
	public function defaultPermalink ($value, $dc)
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
	public function generatePermalink ($dc)
	{
		try
		{
			\System::getContainer()->get('contao.permalink.generator')->generate($dc);
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

		$url =  \System::getContainer()->get('contao.permalink.generator')->getUrl($dc);
		
		if(null !== $url)
		{
			$this->saveAlias($url->getPath(), $dc->id, $dc->table);
		}
	}
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function saveAlias ($alias, $intId, $strTable)
	{
		$db = \Database::getInstance();
		
		if (empty($alias))
		{
			$alias = 'index';
		}
		
		$db->execute("UPDATE $strTable SET alias='$alias' WHERE id='$intId'");
	}
	
	
	
	/**
	 * Add extra css and js to the backend template
	 */
	protected function addPermalinkField ($strTable)
	{
		$context = \System::getContainer()->get('contao.permalink.generator')->getContextForTable($strTable);
		
		$GLOBALS['TL_DCA'][$strTable]['config']['onload_callback'][] = array('Agoat\\Permalink\\DataContainer', 'modifyPalette');
		$GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = array('Agoat\\Permalink\\DataContainer', 'generatePermalink');
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
				array('Agoat\\Permalink\\DataContainer', 'defaultPermalink')
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
		$db = \Database::getInstance();
		
		$providers = (array) \System::getContainer()->get('contao.permalink.generator')->getProviders();
dump($providers);
		foreach($providers as $context=>$provider)
		{
			if ($db->tableExists($provider->getDcaTable()))
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
		}
		
		$pattern = ['/,useAutoItem/', '/,folderUrl/', '/({frontend_legend}.*?);/'];
		$replace = ['', '', '$1'.$palette.';'];
		
		$GLOBALS['TL_DCA'][$strTable]['palettes']['default'] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA'][$strTable]['palettes']['default']);
		
	}

	
}
