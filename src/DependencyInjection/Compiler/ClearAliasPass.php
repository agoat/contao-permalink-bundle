<?php
 
 /**
 * Contao Open Source CMS - Permalink extension
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   permalink
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Sets the available Imagine class name in the container.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class ClearAliasPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // TODO: Empty all alias fields in all tables
    }
}