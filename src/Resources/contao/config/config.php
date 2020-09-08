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


// Add loadDataContainer hook
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Agoat\\PermalinkBundle\\Contao\\DataContainer','onLoadDataContainer');


// Add widget
$GLOBALS['BE_FFL']['permalinkWizard'] = '\Agoat\PermalinkBundle\Contao\PermalinkWizard';


// Style sheet
if (defined('TL_MODE') && TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'bundles/agoatpermalink/permalink.css|static';
}
