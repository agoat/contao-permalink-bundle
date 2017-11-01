<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
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
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        // Check for supported context
		
		$this->context = $context;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function setSource($source)
    {
        $source = (int) $source;
		
		$this->source = $source;

        return $this;
    }

	
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setGuid($guid)
    {
        list($host, $path) = explode('/', $guid, 2);
		
		$this->host = $host;
		$this->path = $path;

        return $this;
    }

 }