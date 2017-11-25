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

 
/**
 * Table tl_content_element
 */
$GLOBALS['TL_DCA']['tl_permalink'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'guid' => 'index',
				'context,source' => 'index'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL auto_increment"
		),
		'guid' => array
		(
			'sql'	=> "varchar(255) NOT NULL default ''"
		),
		'context' => array
		(
			'sql'	=> "varchar(32) NOT NULL default ''"
		),
		'source' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
	)
);
