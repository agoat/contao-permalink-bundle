<?php

/*
 * This file is part of the contao permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Routing;

use Agoat\PermalinkBundle\Controller\ControllerChain;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RequestContext;


/**
 * Generates Contao URLs.
 *
 * @author Arne Stappen <https://github.com/agoat>
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var ControllerChain
     */
    private $controllerChain;

    /**
     * @var bool
     */
    private $prependLocale;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface    $router
     * @param ContaoFrameworkInterface $framework
     * @param bool                     $prependLocale
     */
    public function __construct(UrlGeneratorInterface $router, ControllerChain $controllerChain)
    {
        $this->router = $router;
        $this->controllerChain = $controllerChain;
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
    public function generate($path, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        //$this->framework->initialize(); // needed??
				
		if (empty($path))
		{
			return '';
		}
		
		foreach ($this->controllerChain->getContexts() as $delimiter)
		{
			if (count($arrPath = explode('/'.$delimiter.'/', $path, 2)) > 1  && false === strpos($path, '%s'))
			{
				$path = $arrPath[1];
				break;
			}
		}
		
		if (!is_array($parameters)) {
			$parameters = [];
		}

		unset($parameters['_locale']); // If the %prependLocale% parameter is set, don't use it

		$context = $this->getContext();

		// Store the original request context
		$host = $context->getHost();
		$scheme = $context->getScheme();
		$httpPort = $context->getHttpPort();
		$httpsPort = $context->getHttpsPort();

		$this->preparePath($path, $parameters);
		$this->prepareDomain($context, $parameters, $referenceType);

		$url = $this->router->generate(
			'index' == $path ? 'contao_guid_root' : 'contao_guid_frontend',
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
     * Adds the parameters to the path.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @throws MissingMandatoryParametersException
     */
    private function preparePath($path, array &$parameters)
    {
        if ('index' == $path) {
            return;
        }

        $parameters['path'] = preg_replace_callback(
            '/\{([^\}]+)\}/',
            function ($matches) use ($path, &$parameters, $config) {
                $param = $matches[1];

                if (!isset($parameters[$param])) {
                    throw new MissingMandatoryParametersException(
                        sprintf('Parameters "%s" is missing to generate a URL for "%s"', $param, $path)
                    );
                }

                $value = $parameters[$param];
                unset($parameters[$param]);


                return $value;
            },
            $path
        );
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
}