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

namespace Agoat\PermalinkBundle;

use Agoat\PermalinkBundle\DependencyInjection\Compiler\ControllerChainPass;
use Agoat\PermalinkBundle\DependencyInjection\Compiler\PermalinkProviderPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Configures the permalink bundle.
 *
 * @author Arne Stappen (alias aGoat) <https://github.com/agoat>
 */
class AgoatPermalinkBundle extends Bundle
{
}
