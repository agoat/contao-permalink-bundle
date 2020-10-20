<?php

declare(strict_types=1);

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

use Contao\Config;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Exception\NoRootPageFoundException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var string
     */
    private $urlSuffix;

    /**
     * @var bool
     */
    private $prependLocale;

    /**
     * @internal Do not inherit from this class; decorate the "contao.routing.route_provider" service instead
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @inheritDoc
     *
     * This is only called if no permalink route matches
     * or by calling contao\frontend::getRootPageFromUrl method (via NestedMatcher)
     */
    public function getRouteCollectionForRequest(Request $request): RouteCollection
    {
        $pathInfo = rawurldecode($request->getPathInfo());

        if ('/' === $pathInfo) {
            return $this->createCollectionForRootPages($request);
        }

        return new RouteCollection();
    }


    /**
     * @inheritDoc
     */
    public function getRouteByName($name): Route
    {
        $defaults = [
            '_token_check' => true,
            '_controller' => 'AgoatPermalinkBundle:Permalink:root',
            '_scope' => ContaoCoreBundle::SCOPE_FRONTEND,
        ];

        // Do not generate individual routes for pages
        // Instead return a Permalink-Root Route (All other possibilities should be caught before)
        return new Route('/', $defaults);
    }

    /**
     * @inheritDoc
     */
    public function getRoutesByNames($names): array
    {
        // Do not generate individual routes for pages
        return [];
    }

    private function createCollectionForRootPages(Request $request): RouteCollection
    {
        $routes = $this->addRoutesForRootPages($this->findRootPages($request->getHttpHost()));

        $this->sortRoutes($routes, $request->getLanguages());

        $collection = new RouteCollection();

        foreach ($routes as $name => $route) {
            $collection->add($name, $route);
        }

        return $collection;
    }

    private function addRoutesForRootPages($rootPages): array
    {
        $routes = [];

        foreach ($rootPages as $page) {
            if ('root' !== $page->type && 'index' !== $page->alias && '/' !== $page->alias) {
                continue;
            }

            $page->loadDetails();

            $path = '/';
            $requirements = [];
            $defaults = $this->getRouteDefaults($page);

            if ($this->prependLocale) {
                $path = '/{_locale}'.$path;
                $requirements['_locale'] = $page->rootLanguage;
            }

            $routes['tl_page.' . $page->id . '.root'] = new Route(
                $path,
                $defaults,
                $requirements,
                [],
                $page->domain,
                $page->rootUseSSL ? 'https' : null,
                []
            );
        }

        return $routes;
    }

    /**
     * @return array<string,PageModel|bool|string>
     */
    private function getRouteDefaults(PageModel $page): array
    {
        return [
            '_token_check' => true,
            '_controller' => 'Contao\FrontendIndex::renderPage',
            '_scope' => ContaoCoreBundle::SCOPE_FRONTEND,
            '_locale' => $page->rootLanguage,
            'pageModel' => $page,
        ];
    }

    /**
     * Sorts routes so that the FinalMatcher will correctly resolve them.
     *
     * 1. The ones with hostname should come first, so the ones with empty host are only taken if no hostname matches
     * 2. Root pages must be sorted by accept language, so the best language matches first
     */
    private function sortRoutes(array &$routes, array $languages = null): void
    {
        // Convert languages array so key is language and value is priority
        if (null !== $languages) {
            foreach ($languages as &$language) {
                $language = str_replace('_', '-', $language);

                if (5 === \strlen($language)) {
                    $lng = substr($language, 0, 2);

                    // Append the language if only language plus dialect is given (see #430)
                    if (!\in_array($lng, $languages, true)) {
                        $languages[] = $lng;
                    }
                }
            }

            unset($language);

            $languages = array_flip(array_values($languages));
        }

        uasort(
            $routes,
            static function (Route $a, Route $b) use ($languages, $routes) {
                if ('' !== $a->getHost() && '' === $b->getHost()) {
                    return -1;
                }

                if ('' === $a->getHost() && '' !== $b->getHost()) {
                    return 1;
                }

                /** @var PageModel $pageA */
                $pageA = $a->getDefault('pageModel');

                /** @var PageModel $pageB */
                $pageB = $b->getDefault('pageModel');

                // Check if the page models are valid (should always be the case, as routes are generated from pages)
                if (!$pageA instanceof PageModel || !$pageB instanceof PageModel) {
                    return 0;
                }

                if (null !== $languages && $pageA->rootLanguage !== $pageB->rootLanguage) {
                    $langA = $languages[$pageA->rootLanguage] ?? null;
                    $langB = $languages[$pageB->rootLanguage] ?? null;

                    if (null === $langA && null === $langB) {
                        if ($pageA->rootIsFallback && !$pageB->rootIsFallback) {
                            return -1;
                        }

                        if ($pageB->rootIsFallback && !$pageA->rootIsFallback) {
                            return 1;
                        }

                        return $pageA->rootSorting <=> $pageB->rootSorting;
                    }

                    if (null === $langA && null !== $langB) {
                        return 1;
                    }

                    if (null !== $langA && null === $langB) {
                        return -1;
                    }

                    if ($langA < $langB) {
                        return -1;
                    }

                    if ($langA > $langB) {
                        return 1;
                    }
                }

                return 0;
            }
        );
    }

    /**
     * @return array<Model>
     */
    private function findRootPages(string $httpHost): array
    {
        if (
            !empty($GLOBALS['TL_HOOKS']['getRootPageFromUrl'])
            && \is_array($GLOBALS['TL_HOOKS']['getRootPageFromUrl'])
        ) {
            /** @var System $system */
            $system = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_HOOKS']['getRootPageFromUrl'] as $callback) {
                $page = $system->importStatic($callback[0])->{$callback[1]}();

                if ($page instanceof PageModel) {
                    return [$page];
                }
            }
        }

        $rootPages = [];

        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class);
        $pages = $pageModel->findBy(["(tl_page.type='root' AND (tl_page.dns=? OR tl_page.dns=''))"], $httpHost);

        if ($pages instanceof Collection) {
            $rootPages = $pages->getModels();
        }

        return $rootPages;
    }
}
