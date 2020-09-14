<?php


namespace Agoat\PermalinkBundle\EventListener\DataContainer;


use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

/**
 * Remove the alias field and add the permalink widget to the palette
 *
 * @internal We can not use the Callback annotation here as the listener is added dynamically (depending on existing permalink handlers)
 */
class OnLoadListener
{
    public function __invoke(DataContainer $dc)
    {
        $pattern = ['/,alias/', '/{title_legend}.*?;/'];
        $replace = ['', '$0{permalink_legend},permalink;'];

        $palettes = array_diff(array_keys($GLOBALS['TL_DCA'][$dc->table]['palettes']), ['__selector__']);

        foreach ($palettes as $palette) {
            $GLOBALS['TL_DCA'][$dc->table]['palettes'][$palette] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA'][$dc->table]['palettes'][$palette]);
        }

        // Add new callback first to be sure this is an array
        $GLOBALS['TL_DCA'][$dc->table]['select']['buttons_callback'][] = [ButtonsListener::class, '__invoke'];

        foreach ($GLOBALS['TL_DCA'][$dc->table]['select']['buttons_callback'] as $k => $v) {
            if ($v[1] == 'addAliasButton') {
                unset($GLOBALS['TL_DCA'][$dc->table]['select']['buttons_callback'][$k]);
                break;
            }
        }
    }
}
