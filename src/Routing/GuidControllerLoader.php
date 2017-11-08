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
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        $defaults = [
            '_token_check' => true,
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
		$defaults['_controller'] = 'AgoatPermalinkBundle:Guid:frontend';

		$route = new Route('/{path}%contao.url_suffix%', $defaults, ['path' => '.+']);

        $routes->add('contao_guid_frontend', $route);
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

        $routes->add('contao_guid_root', $route);
    }
}