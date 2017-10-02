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


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Agoat\\Permalink\\DataContainer','onLoadDataContainer');
