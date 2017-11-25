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

namespace Agoat\PermalinkBundle\Controller;

use Symfony\Component\HttpFoundation\Request;


/**
 * Controller provider interface
 */
interface ControllerInterface
{

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getDcaTable();

	
	/**
     * Runs the controller and generate the response
     *
     * @param integer $source
	 * @param Request $request
	 *
     * @return Response
     */
    public function run($source, Request $request);
}
 