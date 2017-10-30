<?php
 
 /**
 * Contao Open Source CMS - ContentBlocks extension
 *
 * Copyright (c) 2016 Arne Stappen (aGoat)
 *
 *
 * @package   contentblocks
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */


// Add loadDataContainer hook
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Agoat\\Permalink\\DataContainer','onLoadDataContainer');

// Add widget
$GLOBALS['BE_FFL'][] = ['permalinkWizard', 'PermalinkWizard'];
$GLOBALS['BE_FFL']['permalinkWizard'] = '\Agoat\Permalink\PermalinkWizard';

// Deactivate useAutoItem by default
\Config::set('useAutoItem', false);


/**
 * Style sheet
 */
if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'bundles/agoatpermalink/style.css|static';
}
