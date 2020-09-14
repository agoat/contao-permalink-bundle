<?php


namespace Agoat\PermalinkBundle\EventListener\DataContainer;

use Agoat\PermalinkBundle\Permalink\Permalink;
use Contao\DataContainer;
use Contao\System;

/**
 * Remove a permalink
 *
 * @internal We can not use the Callback annotation here as the listener is added dynamically (depending on existing permalink handlers)
 */
class OnDeleteListener
{
    /** @var Permalink */
    protected $permalink;

    public function __construct(Permalink $permalink)
    {
        $this->permalink = $permalink;
    }

    public function __invoke(DataContainer $dc)
    {
        $this->permalink->remove($dc);
    }
}
