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

namespace Agoat\PermalinkBundle\Contao;

use Contao\Controller as ContaoController;
use Contao\CoreBundle\Exception\AccessDeniedException;


/**
 * Datacontainer class
 */
class DataContainer extends ContaoController
{
	
	/**
	 * Add the permalink settings for supported tables
	 *
	 * @param string $strTable
	 */
	public function onLoadDataContainer ($strTable)
	{
		if ('FE' == TL_MODE)
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
	 * Set the default value from the dca array
	 *
	 * @param string|mixed  $value
	 * @param DataContainer $dc
	 */
	public function defaultValue ($value, $dc)
	{
		if (empty($value))
		{
			$value = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['default'];
		}
		
		return $value;
	}
	

	/**
	 * Generate a permalink
	 *
	 * @param DataContainer $dc
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
	 * Remove a permalink
	 *
	 * @param DataContainer $dc
	 */
	public function removePermalink ($dc)
	{
		\System::getContainer()->get('contao.permalink.generator')->remove($dc);
	}
	
	
	/**
	 * Save the alias to the database
	 *
	 * @param string  $alias
	 * @param integer $intId
	 * @param string  $strTable
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
	 * Add a permalink field to the dca array
	 *
	 * @param string $strTable
	 */
	protected function addPermalinkField ($strTable)
	{
		$context = \System::getContainer()->get('contao.permalink.generator')->getContextForTable($strTable);
		
		$GLOBALS['TL_DCA'][$strTable]['config']['onload_callback'][] = array('Agoat\\PermalinkBundle\\Contao\\DataContainer', 'modifyPalette');
		$GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = array('Agoat\\PermalinkBundle\\Contao\\DataContainer', 'generatePermalink');
		$GLOBALS['TL_DCA'][$strTable]['config']['ondelete_callback'][] = array('Agoat\\PermalinkBundle\\Contao\\DataContainer', 'removePermalink');

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
				array('Agoat\\PermalinkBundle\\Contao\\DataContainer', 'defaultValue')
			),
			'sql'			=> "varchar(128) COLLATE utf8_bin NOT NULL default ''"
		);
		
		$GLOBALS['TL_LANG'][$strTable]['permalink'] = $GLOBALS['TL_LANG']['DCA']['permalink'];
		$GLOBALS['TL_LANG'][$strTable]['permalink_legend'] = $GLOBALS['TL_LANG']['DCA']['permalink_legend'];
	}

	
	/**
	 * Remove the alias field and add the permalink widget to the palette
	 *
	 * @param DataContainer $dc
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

		$GLOBALS['TL_DCA'][$dc->table]['select']['buttons_callback'][] = array('Agoat\\PermalinkBundle\\Contao\\DataContainer', 'addPermlinkButton');

		foreach ($GLOBALS['TL_DCA'][$dc->table]['select']['buttons_callback'] as $k=>$v)
		{
			if ($v[1] == 'addAliasButton')
			{
				unset($GLOBALS['TL_DCA'][$dc->table]['select']['buttons_callback'][$k]);
			}
		}
	}
	
	
	/**
	 * Automatically generate permalinks for
	 *
	 * @param array         $arrButtons
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function addPermlinkButton ($arrButtons, $dc)
	{
		// Generate/update the permalinks
		if (\Input::post('FORM_SUBMIT') == 'tl_select' && isset($_POST['permalink']))
		{
			/** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
			$objSession = \System::getContainer()->get('session');

			$session = $objSession->all();
			$ids = $session['CURRENT']['IDS'];

			$db = \Database::getInstance();
	
			foreach ($ids as $id)
			{
				$dc->id = $id;
				
				$db->prepare("UPDATE $dc->table SET permalink=? WHERE id='$id' and permalink=''")->execute($GLOBALS['TL_DCA'][$dc->table]['fields']['permalink']['default']);

				try
				{
					\System::getContainer()->get('contao.permalink.generator')->generate($dc);
				}
				catch (ResponseException $e)
				{
					throw $e;
				}
				catch (\Exception $e) {}
				
				$url =  \System::getContainer()->get('contao.permalink.generator')->getUrl($dc);

				$alias = $db->execute("SELECT alias FROM $dc->table WHERE id='$id'");
			
				if(null !== $url && null !== $alias && $url->getPath() != $alias->alias)
				{
					$objVersions = new \Versions($dc->table, $id);
					$objVersions->initialize();
					
					$this->saveAlias($url->getPath(), $id, $dc->table);
					
					$objVersions->create();
				}
			}

			$this->redirect($this->getReferer());
		}
		
		// Add the button
		$arrButtons['permalink'] = '<button type="submit" name="permalink" id="permalink" class="tl_submit" accesskey="p">'.$GLOBALS['TL_LANG']['MSC']['permalinkSelected'].'</button> ';
		
		return $arrButtons;	
	}
	
	
	/**
	 * Add permalink default pattern text fields
	 *
	 * @param string $strTable
	 */
	public function addPermalinkSettings ($strTable)
	{
		$db = \Database::getInstance();
		
		$providers = (array) \System::getContainer()->get('contao.permalink.generator')->getProviders();

		foreach($providers as $context=>$provider)
		{
			if ($db->tableExists($provider->getDcaTable()))
			{
				$GLOBALS['TL_DCA']['tl_settings']['fields'][$context.'Permalink'] = array
				(
					'label'			=> &$GLOBALS['TL_LANG']['tl_settings'][$context.'Permalink'],
					'default'		=> \System::getContainer()->getParameter('contao.permalink.'.$context),
					'inputType'		=> 'text',
					'eval'			=> array('tl_class'=>'w50'),
					'save_callback' => array
					(
						array('Agoat\\PermalinkBundle\\Contao\\DataContainer', 'defaultValue')
					),
				);
				
				$palette .= ','.$context.'Permalink';
			}
		}
		
		$pattern = ['/,useAutoItem/', '/,folderUrl/', '/({frontend_legend}.*?);/'];
		$replace = ['', '', '$1'.$palette.';'];
		
		$GLOBALS['TL_DCA'][$strTable]['palettes']['default'] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA'][$strTable]['palettes']['default']);
	}
}
