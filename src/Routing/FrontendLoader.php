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

namespace Agoat\PermalinkBundle\Routing;

use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Contao\CoreBundle\Routing\FrontendLoader as ContaoFrontendLoader;


/**
 * Adds permalink routes for the Contao front end
 */
class FrontendLoader extends ContaoFrontendLoader
{
    public function __construct() {}

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): RouteCollection
    {
        $routes = new RouteCollection();

        $defaults = [
            '_token_check' => true,
             '_scope' => ContaoCoreBundle::SCOPE_FRONTEND
        ];

        $this->addPermalinkRoute($routes, $defaults);
        $this->addRootRoute($routes, $defaults);

        return $routes;
    }


    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return in_array($type, ['contao_permalink', 'contao_frontend']);
    }


    /**
     * Adds the permalink route
     *
     * @param RouteCollection $routes
     * @param array           $defaults
     */
    private function addPermalinkRoute(RouteCollection $routes, array $defaults)
    {
		$defaults['_controller'] = 'AgoatPermalinkBundle:Permalink:guid';

		$route = new Route('/{path}%contao.url_suffix%', $defaults, ['path' => '.+']);

        $routes->add('contao_permalink', $route);
    }


    /**
     * Adds a root route
     *
     * @param RouteCollection $routes
     * @param array           $defaults
     */
    private function addRootRoute(RouteCollection $routes, array $defaults)
    {
		$defaults['_controller'] = 'AgoatPermalinkBundle:Permalink:root';

		$route = new Route('/', $defaults);

        $routes->add('contao_permalink_root', $route);
    }
}
