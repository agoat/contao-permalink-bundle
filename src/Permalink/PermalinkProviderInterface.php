<?php

/*
 * This file is part of the permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Permalink;



/**
 * Controller provider interface.
 *
 * @author Arne Stappen <https://github.com/agoat>
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
     * Returns the default substitute settings
     *
     * @return string
     */
    public function getHost($id);

    /**
     * Returns the default substitute settings
     *
     * @return string
     */
    public function getSchema($id);


 }