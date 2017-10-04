<?php

/*
 * This file is part of the contao permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Routing;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;


/**
 * Generates Contao URLs.
 *
 * @author Arne Stappen <https://github.com/agoat>
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class UrlGenerator implements UrlGeneratorInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var bool
     */
    private $prependLocale;

    /**
     * @var UrlGeneratorInterface
     */
    private $legacy;

    /**
     * @var UrlConfigurationInterface
     */
    private $urlConfig;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface    $router
     * @param ContaoFrameworkInterface $framework
     * @param bool                     $prependLocale
     */
    public function __construct(UrlGeneratorInterface $router, ContaoFrameworkInterface $framework, $prependLocale, UrlGeneratorInterface $legacy)
    {
        $this->router = $router;
        $this->framework = $framework;
        $this->prependLocale = $prependLocale;
        $this->legacy = $legacy;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * Generates a Frontend URL.
     *
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string
     */
    public function generate($varAlias, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $this->framework->initialize();

dump($varAlias);					
		if (is_string($varAlias))
		{
			if (empty($varAlias))
			{
				return '';
			}

			list($page, $controller, $source, $vars) = explode('/', $varAlias, 3);

			if (!is_numeric($page))
			{
	dump('LEGACY');			
				// Legacy mode (old alias given)
				return $this->legacy->generate($varAlias);
			}
			

			if ($this->contextExists($controller) && $source)
			{
				$this->urlConfig = array
				(
					'controller' => $controller,
					'source' => $source
				);
			}
			else
			{
				$this->urlConfig = array
				(
					'controller' => 'page',
					'source' => $page
				);
			}

			if (null !== $vars)
			{
				$this->urlConfig['get'] = $vars;
			}
		}
		
		// The $varAlias should be a UrlConfigurationInterface instance
		//
		// $varAlias = new UrlConfiguration()
		// $varAlias->getController();
		// $varAlias->getSource();
		// $varAlias->getGet('param1');
		//
		// $varAlias->setController('page');
		// $varAlias->setSource($intId);
		// $varAlias->setGet('param1', 'value');
		
dump($this->urlConfig);		
		
	



		if (is_numeric($source))
		{
			$objPermalink = \PermalinkModel::findByControllerAndSource($controller, $source);

		}
		else
		{
			$objPermalink = \PermalinkModel::findByControllerAndSource('page', $page);

		}
		
	dump($objPermalink);
		if (null === $objPermalink)
		{
			// Try the old way (legacy mode)
			return $this->legacy->generate($varAlias);
		}
		else
		{
			$name = $objPermalink->alias . ($params ? '/' . $params : '');
		}

	
		if (!is_array($parameters)) {
            $parameters = [];
        }

        $context = $this->getContext();

        // Store the original request context
        $host = $context->getHost();
        $scheme = $context->getScheme();
        $httpPort = $context->getHttpPort();
        $httpsPort = $context->getHttpsPort();

        $this->prepareLocale($parameters);
        $this->prepareAlias($name, $parameters);
        $this->prepareDomain($context, $parameters, $referenceType);

        unset($parameters['auto_item']);

        $url = $this->router->generate(
            'index' === $name ? 'contao_index' : 'contao_frontend',
            $parameters,
            $referenceType
        );

        // Reset the request context
        $context->setHost($host);
        $context->setScheme($scheme);
        $context->setHttpPort($httpPort);
        $context->setHttpsPort($httpsPort);

        return $url;
    }

    /**
     * Removes the locale parameter if it is disabled.
     *
     * @param array $parameters
     */
    private function prepareLocale(array &$parameters)
    {
        if (!$this->prependLocale && array_key_exists('_locale', $parameters)) {
            unset($parameters['_locale']);
        }
    }

    /**
     * Adds the parameters to the alias.
     *
     * @param string $alias
     * @param array  $parameters
     *
     * @throws MissingMandatoryParametersException
     */
    private function prepareAlias($alias, array &$parameters)
    {
        if ('index' === $alias) {
            return;
        }

        $parameters['alias'] = preg_replace_callback(
            '/\{([^\}]+)\}/',
            function ($matches) use ($alias, &$parameters, $config) {
                $param = $matches[1];

                if (!isset($parameters[$param])) {
                    throw new MissingMandatoryParametersException(
                        sprintf('Parameters "%s" is missing to generate a URL for "%s"', $param, $alias)
                    );
                }

                $value = $parameters[$param];
                unset($parameters[$param]);


                return $value;
            },
            $alias
        );
    }

    /**
     * Forces the router to add the host if necessary.
     *
     * @param RequestContext $context
     * @param array          $parameters
     * @param int            $referenceType
     */
    private function contextExists($controller)
    {
        return in_array($controller, ['page', 'articles', 'items', 'events']);
    }

    /**
     * Forces the router to add the host if necessary.
     *
     * @param RequestContext $context
     * @param array          $parameters
     * @param int            $referenceType
     */
    private function prepareDomain(RequestContext $context, array &$parameters, &$referenceType)
    {
        if (isset($parameters['_ssl'])) {
            $context->setScheme(true === $parameters['_ssl'] ? 'https' : 'http');
        }

        if (isset($parameters['_domain']) && '' !== $parameters['_domain']) {
            $this->addHostToContext($context, $parameters, $referenceType);
        }

        unset($parameters['_domain'], $parameters['_ssl']);
    }

    /**
     * Sets the context from the domain.
     *
     * @param RequestContext $context
     * @param array          $parameters
     * @param string         $referenceType
     */
    private function addHostToContext(RequestContext $context, array $parameters, &$referenceType)
    {
        list($host, $port) = $this->getHostAndPort($parameters['_domain']);

        if ($context->getHost() === $host) {
            return;
        }

        $context->setHost($host);
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;

        if (!$port) {
            return;
        }

        if (isset($parameters['_ssl']) && true === $parameters['_ssl']) {
            $context->setHttpsPort($port);
        } else {
            $context->setHttpPort($port);
        }
    }

    /**
     * Extracts host and port from the domain.
     *
     * @param $domain
     *
     * @return array
     */
    private function getHostAndPort($domain)
    {
        if (false !== strpos($domain, ':')) {
            return explode(':', $domain, 2);
        }

        return [$domain, null];
    }

    /**
     * Returns the auto_item key from the parameters or the global array.
     *
     * @param array $parameters
     *
     * @return array
     */
    private function getAutoItems(array $parameters)
    {
        if (isset($parameters['auto_item'])) {
            return [$parameters['auto_item']];
        }

        if (isset($GLOBALS['TL_AUTO_ITEM']) && is_array($GLOBALS['TL_AUTO_ITEM'])) {
            return $GLOBALS['TL_AUTO_ITEM'];
        }

        return [];
    }

}