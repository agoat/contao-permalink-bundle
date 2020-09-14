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

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\DataContainer;
use Contao\PageModel;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;


/**
 * Permalink generator
 */
class Permalink
{
    /** @var PermalinkHandlerInterface[] */
    private $handlerByTable = [];

    /** @var PermalinkHandlerInterface[] */
    private $handlerByContext = [];

    /**
     * PermalinkGenerator constructor.
     * @param iterable $permalinkhandler
     */
    public function __construct(iterable $permalinkhandlers)
    {
        /** @var PermalinkHandlerInterface $handler */
        foreach (iterator_to_array($permalinkhandlers) as $handler) {
            $this->handlerByTable[$handler::getDcaTable()] = $handler;
            $this->handlerByContext[$handler::getContext()] = $handler;
        }
    }

    /**
	 * Returns whether the given table is supported
	 *
     * @param string $table
	 *
	 * @return boolean
     */
	public function supportsContext(string $context)
	{
		return array_key_exists($context, $this->handlerByContext);
	}

   /**
	 * Returns whether the given table is supported
	 *
     * @param string $table
	 *
	 * @return boolean
     */
	public function supportsTable(string $table)
	{
		return array_key_exists($table, $this->handlerByTable);
	}

    /**
	 * Return all supported Contexts
	 *
	 * @return array
     */
	public function getSupportedContexts()
	{
	    return array_keys($this->handlerByContext);
	}

    /**
	 * Return all supported dca tables
	 *
	 * @return array
     */
	public function getSupportedTables()
	{
	    return array_keys($this->handlerByTable);
	}


    /**
     * Generate the permalink for the current object
     *
     * @param string $context
     * @param integer $id
     * @param Request $request
     *
     * @return \PageModel
     *
     * @throws PageNotFoundException
     */
	public function findPage(string $context, int $id, Request $request): PageModel
	{
	    return $this->handlerByContext[$context]->findPage($id, $request);
	}

   /**
     * Generate the permalink for the current object
     *
     * @param DataContainer $dc
     */
	public function generate(DataContainer $dc)
	{
	    $this->handlerByTable[$dc->table]->generate($dc->id);
	}


    /**
     * Remove the permalink of the current object
     *
     * @param DataContainer $dc
     *
     * @return boolean
     */
	public function remove(DataContainer $dc)
	{
        $this->handlerByTable[$dc->table]->remove($dc->id);
	}


    /**
	 * Return the url for the current object
	 *
     * @param DataContainer $dc
	 *
     * @return PermalinkUrl
     */
	public function getUrl(DataContainer $dc)
	{
        return $this->handlerByTable[$dc->table]->getUrl($dc->id);
	}


	public function getContext(string $table)
    {
        return $this->handlerByTable[$table]::getContext();
    }

	public function getTable(string $context)
    {
        return $this->handlerByContext[$context]::getDcaTable();
    }

    public function getDefault(string $table)
    {
        return $this->handlerByTable[$table]::getDefault();
    }
}
