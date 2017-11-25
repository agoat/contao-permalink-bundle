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

namespace Agoat\PermalinkBundle\Permalink;

use Contao\CoreBundle\Exception\AccessDeniedException;


class PermalinkUrl
{
    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @var string
     */
    private $context;

    /**
     * @var int
     */
    private $source;


    /**
     * Return the scheme
	 *
	 * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }


    /**
     * Set the scheme
	 *
	 * @param string $scheme
	 *
	 * @return PermalinkUrl
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }


    /**
     * Return the host
	 *
	 * @return string
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * Set the host
	 *
	 * @param string $host
	 *
	 * @return PermalinkUrl
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }


    /**
     * Return the path
	 *
	 * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * Set the path
	 *
	 * @param string $path
	 *
	 * @return PermalinkUrl
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }


    /**
     * Return the suffix
	 *
	 * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }


    /**
     * Set the suffix
	 *
	 * @param string $suffix
	 *
	 * @return PermalinkUrl
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }


    /**
     * Return the context
	 *
	 * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

 
    /**
     * Set the context
	 *
	 * @param string $context
	 *
	 * @return PermalinkUrl
     */
    public function setContext($context)
    {
        // Todo: check for supported context
		$this->context = $context;

        return $this;
    }


    /**
     * Return the source id
	 *
	 * @return integer
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Set the source id
	 *
	 * @param integer $source
	 *
	 * @return PermalinkUrl
     */
    public function setSource($source)
    {
        $source = (int) $source;
		
		$this->source = $source;

        return $this;
    }

	
    /**
     * Return the guid (host/path)
	 *
	 * @return string
     */
    public function getGuid()
    {
        if (empty($this->path))
		{
			return $this->host;
		}
		else
		{
			return $this->host . '/' . $this->path;
		}
    }


    /**
     * Set the guid (host/path)
	 *
	 * @param string $guid
	 *
	 * @return PermalinkUrl
     */
    public function setGuid($guid)
    {
        list($host, $path) = explode('/', $guid, 2);
		
		$this->host = $host;
		$this->path = $path;

        return $this;
    }
}
