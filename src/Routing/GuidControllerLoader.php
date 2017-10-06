<?php

/*
 * This file is part of the contao permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Routing;

use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Adds routes for the Contao front end.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class GuidControllerLoader extends Loader
{
    /**
     * @var bool
     */
    private $prependLocale;

    /**
     * Constructor.
     *
     * @param bool $prependLocale
     */
    public function __construct($prependLocale)
    {
        $this->prependLocale = $prependLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $defaults = [
            '_token_check' => true,
            '_controller' => 'AgoatPermalinkBundle:Guid:frontend',
            '_scope' => ContaoCoreBundle::SCOPE_FRONTEND
        ];

        $this->addFrontendRoute($routes, $defaults);
        $this->addRootRoute($routes, $defaults);

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'contao_frontend' === $type;
    }

    /**
     * Adds the frontend route, which is language-aware.
     *
     * @param RouteCollection $routes
     * @param array           $defaults
     */
    private function addFrontendRoute(RouteCollection $routes, array $defaults)
    {
		$route = new Route('/{alias}%contao.url_suffix%', $defaults, ['alias' => '.+']);

        $this->addLocaleToRoute($route);

        $routes->add('contao_frontend', $route);
    }

    /**
     * Adds a route to redirect a user to the index page.
     *
     * @param RouteCollection $routes
     * @param array           $defaults
     */
    private function addRootRoute(RouteCollection $routes, array $defaults)
    {
		$defaults['_controller'] = 'AgoatPermalinkBundle:Guid:root';

		$route = new Route('/', $defaults);

        $this->addLocaleToRoute($route);

        $routes->add('contao_root', $route);
    }

    /**
     * Adds the locale to the route if prepend_locale is enabled.
     *
     * @param Route $route
     */
    private function addLocaleToRoute(Route $route)
    {
        if ($this->prependLocale) {
            $route->setPath('/{_locale}'.$route->getPath());
            $route->addRequirements(['_locale' => '[a-z]{2}(\-[A-Z]{2})?']);
        } else {
            $route->addDefaults(['_locale' => null]);
        }
    }
}