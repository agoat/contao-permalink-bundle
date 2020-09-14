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
use Symfony\Component\HttpFoundation\Request;

/**
 * Permalink provider interface
 */
interface PermalinkHandlerInterface
{
   /**
     * Returns the supported context
     *
     * @return string
     */
    public static function getContext(): string;

    /**
     * Returns the table name
     *
     * @return string
     */
    public static function getDcaTable(): string;

   /**
     * Returns the default permalink logic string
     *
     * @return string
     */
    public static function getDefault(): string;

    /**
     * Find the corresponding page
     *
     * @param integer $id
     * @param Request $request
     *
     * @return \PageModel
     *
     * @throws PageNotFoundException
     */
    public function findPage(int $id, Request $request);

	/**
     * Generate and save a permalink
     *
     * @param integer $source
     *
     * @thows AccessDeniedException
     */
    public function generate($source);


    /**
     * Remove a permalink
     *
     * @param integer $source
     *
     * @return boolean
     */
    public function remove($source);


    /**
     * Get the full permalink url
     *
     * @param integer $source
     *
     * @return PermalinkUrl
     */
    public function getUrl($source);
}
