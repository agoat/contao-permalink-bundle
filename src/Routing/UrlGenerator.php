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

use Agoat\PermalinkBundle\Controller\ControllerLocator;
use Agoat\PermalinkBundle\Permalink\Permalink;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RequestContext;


/**
 * Generates Contao permalink Urls
 */
class UrlGenerator implements UrlGeneratorInterface
{

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Permalink
     */
    private $permalink;

    /**
     * Constructor
     *
     * @param UrlGeneratorInterface $router
     * @param ServiceLocator $permalinkControllerServiceLocator
     */
    public function __construct(UrlGeneratorInterface $router, Permalink $permalink)
    {
        $this->router = $router;
        $this->permalink = $permalink;
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
     * Generates a Frontend URL
     *
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string
     */
    public function generate($path, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
		if (empty($path)) {
			return '';
		}

		// Extract the path after the auto_item (Is this necessary??)
		foreach ($this->permalink->getSupportedContexts() as $delimiter) {
			if (count($arrPath = explode('/' . $delimiter . '/', $path, 2)) > 1  && strpos($path, '%s') === false) {
				$path = $arrPath[1];
				break;
			}
		}

		if (!is_array($parameters)) {
			$parameters = [];
		}

		unset($parameters['_locale']); // The locale is handled with the permalink internally

		$context = $this->getContext();

		// Store the original request context
		$host = $context->getHost();
		$scheme = $context->getScheme();
		$httpPort = $context->getHttpPort();
		$httpsPort = $context->getHttpsPort();

		$this->preparePath($path, $parameters);
		$this->prepareDomain($context, $parameters, $referenceType);

		$url = $this->router->generate(
			'index' == $path ? 'contao_permalink_root' : 'contao_permalink',
			$parameters,
			$referenceType
		);

		// Reset the request context
		$context->setHost($host);
		$context->setScheme($scheme);
		$context->setHttpPort($httpPort);
		$context->setHttpsPort($httpsPort);

		// Restore some allowed character because the symfony UrlGenerator rawencoded them
		$url = strtr($url, ['%24'=>'$', '%28'=>'(', '%29'=>')']);

		return $url;
    }


     /**
     * Adds the parameters to the path
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
            function ($matches) use ($path, &$parameters) {
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
     * Forces the router to add the host if necessary
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
     * Sets the context from the domain
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
     * Extracts host and port from the domain
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
