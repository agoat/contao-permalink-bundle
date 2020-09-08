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

use Contao\DataContainer;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ServiceLocator;


/**
 * Permalink generator
 */
class PermalinkGenerator
{
    /**
     * @var array
     */
    private $permalinkHandlers = [];

    /**
     * PermalinkGenerator constructor.
     * @param iterable $permalinkHandlers
     */
    public function __construct(iterable $permalinkHandlers)
    {
        foreach (iterator_to_array($permalinkHandlers) as $handler) {
            $this->permalinkHandlers[$handler->getDcaTable()] = $handler;
        }
    }

    /**
	 * Returns whether the given table is supported
	 *
     * @param string $table
	 *
	 * @return boolean
     */
	public function supportsTable($table)
	{
		return array_key_exists($table, $this->permalinkHandlers);
	}


    /**
	 * Return all registered providers
	 *
	 * @return array
     */
	public function getHandlers()
	{
	    return $this->permalinkHandlers;
	}


    /**
     * Generate the permalink for the current object
     *
     * @param DataContainer $dc
     */
	public function generate(DataContainer $dc)
	{
	    $this->permalinkHandlers[$dc->table]->generate($dc->id);
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
        $this->permalinkHandlers[$dc->table]->remove($dc->id);
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
        return $this->permalinkHandlers[$dc->table]->getUrl($dc->id);
	}
}
