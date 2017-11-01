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

	
	public function generate(DataContainer $dc)
	{
		$context = $this->context[$dc->table];
		
		if (null === $dc->activeRecord)
		{
			//throw error;
		}
		
		return $this->providers[$context]->generate($context, $dc->id);
	}

	
	public function getUrl(DataContainer $dc)
	{
		$context = $this->context[$dc->table];
		
		if (null === $dc->activeRecord)
		{
			//throw error;
		}

		return $this->providers[$context]->getUrl($context, $dc->id);
	}	
}