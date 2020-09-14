<?php


namespace Agoat\PermalinkBundle\EventListener\DataContainer;


use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

/**
 * Set the default value from the dca array
 *
 * @internal We can not use the Callback annotation here as the listener is added dynamically (depending on existing permalink handlers)
 */
class SaveListener
{
    public function __invoke($value, DataContainer $dc)
    {
        if (empty($value)) {
            $value = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['default'];
        }

        return $value;
    }
}
