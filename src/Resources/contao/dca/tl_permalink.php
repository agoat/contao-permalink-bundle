<?php
 
 /**
 * Contao Open Source CMS - Posts'n'Pages extensino
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   postsnpages
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
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
				'controller,source' => 'index'
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
		'alias' => array
		(
			'sql'	=> "varchar(255) NOT NULL default ''"
		),
		'fragments' => array
		(
			'sql'	=> "varchar(128) NOT NULL default ''"
		),
		'controller' => array
		(
			'sql'	=> "varchar(32) NOT NULL default ''"
		),
		'source' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
	)
);



