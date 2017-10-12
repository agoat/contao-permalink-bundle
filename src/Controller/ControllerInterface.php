<?php

/*
 * This file is part of the permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Controller;

use Symfony\Component\HttpFoundation\Request;


/**
 * Controller provider interface.
 *
 * @author Arne Stappen <https://github.com/agoat>
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
     * @return Response
     */
    public function run($source, Request $request);

 }