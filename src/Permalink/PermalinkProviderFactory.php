<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Permalink;



/**
 * Main front end controller.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PermalinkProviderFactory
{
	protected $suffix;
	
	public function __construct($suffix)
	{
		$this->suffix = $suffix;
	}
}