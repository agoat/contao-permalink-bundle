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

use Agoat\PermalinkBundle\Model\PermalinkModel;
use Agoat\PermalinkBundle\Widget\PermalinkWidget;

$GLOBALS['TL_MODELS']['tl_permalink'] = PermalinkModel::class;
$GLOBALS['BE_FFL']['permalink'] = PermalinkWidget::class;

if (defined('TL_MODE') && TL_MODE == 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/agoatpermalink/permalink.css|static';
}

