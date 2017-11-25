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


/**
 * Permalink provider interface
 */
interface PermalinkProviderInterface
{

   /**
     * Returns the table name
     *
     * @return string
     */
    public function getDcaTable();

 
	/**
     * Generate and save a permalink
     *
     * @param string  $context
     * @param integer $source
     *
     * @thows AccessDeniedException
     */
    public function generate($context, $source);


    /**
     * Remove a permalink
     *
     * @param string  $context
     * @param integer $source
     *
     * @return boolean
     */
    public function remove($context, $source);

	
    /**
     * Get the full permalink url
     *
     * @param string  $context
     * @param integer $source
     *
     * @return PermalinkUrl
     */
    public function getUrl($context, $source);
}
 