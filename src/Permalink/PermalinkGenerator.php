<?php

/*
 * This file is part of the permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Permalink;

use Contao\DataContainer;


/**
 * Handles the Contao frontend routes.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class PermalinkGenerator
{
   /**
     * @var array
     */
	private $providers;

	private $context;

	
	public function __construct()
	{
		$this->providers = array();
		$this->context = array();
	}

	
	public function addProvider(PermalinkProviderInterface $provider, $context)
	{
		$this->providers[$context] = $provider;
		$this->context[$provider->getDcaTable()] = $context;
	}
	
	
	public function supportsTable($table)
	{
		return array_key_exists($table, $this->context);
	}

	
	public function getProviders()
	{
		return $this->providers;
	}

	
	public function getContextForTable($table)
	{
		return $this->context[$table];
	}
	

	
	public function createAlias(DataContainer $dc)
	{
		$context = $this->context[$dc->table];
		
		return $this->providers[$context]->createAlias($dc->activeRecord);
	}

	
	
	
	public function generatePermalink($context, $source)
	{
		$context = $this->context[$dc->table];
		
		return $this->providers[$context]->generatePermalink($dc->activeRecord);
	}
	
	
	public function getAbsoluteUrl($context, $source)
	{
		return $this->providers[$context]->getAbsoluteUrl($source);
	}
	
	
	
	
	
	public function getHost(DataContainer $dc)
	{
		$context = $this->context[$dc->table];

		return $this->providers[$context]->getHost($dc->activeRecord);
	}
	
	// id
	public function getSchema($context, $id)
	{
		return $this->providers[$context]->getSchema($id);
	}
	
	// id
	public function getLanguage()
	{
		$provider = $this->providers[$this->context[$activeRecord->table]];

		if ($provider)
		{
			return $provider->getLanguage($this->activeRecord->id);
		}
	}
	
}