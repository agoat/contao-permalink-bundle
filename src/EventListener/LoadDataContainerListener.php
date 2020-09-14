<?php


namespace Agoat\PermalinkBundle\EventListener;

use Agoat\PermalinkBundle\EventListener\DataContainer\OnDeleteListener;
use Agoat\PermalinkBundle\EventListener\DataContainer\OnLoadListener;
use Agoat\PermalinkBundle\EventListener\DataContainer\OnSubmitListener;
use Agoat\PermalinkBundle\EventListener\DataContainer\SaveListener;
use Agoat\PermalinkBundle\Permalink\AbstractPermalinkHandler;
use Agoat\PermalinkBundle\Permalink\Permalink;
use Agoat\PermalinkBundle\Permalink\PermalinkHandlerInterface;
use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\System;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Add permalink field to the dca for supported tables
 *
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    /** @var Permalink */
    private $permalink;

    /**
     * PermalinkController constructor.
     */
    public function __construct(Permalink $permalink)
    {
        $this->permalink = $permalink;
    }

    public function __invoke(string $table): void
    {
       if (TL_MODE === 'FE') {
            return;
        }

        // Remove the url settings (and add default permalink syntax)
        if ($table === 'tl_settings') {
            $this->addPermalinkSettings();
            return;
        }

        // Add the permalink url structure
        if ($this->permalink->supportsTable($table)) {
            $this->addPermalinkField($table);
        }
    }

    /**
     * Add a permalink field to the dca array
     *
     * @param string $table
     */
    protected function addPermalinkField(string $table)
    {
        $GLOBALS['TL_DCA'][$table]['config']['onload_callback'][] = [OnLoadListener::class, '__invoke'];
        $GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'][] = [OnSubmitListener::class, '__invoke'];
        $GLOBALS['TL_DCA'][$table]['config']['ondelete_callback'][] = [OnDeleteListener::class, '__invoke'];

        $GLOBALS['TL_DCA'][$table]['fields']['permalink'] = array
        (
            'label'			=> &$GLOBALS['TL_LANG'][$table]['permalink'],
            'explanation'	=> $table,
            'default'		=> Config::get($this->permalink->getContext($table) . 'Permalink') ?: $this->permalink->getDefault($table),
            'exclude'		=> true,
            'search'		=> true,
            'inputType'		=> 'permalink',
            'eval'			=> ['mandatory'=>false, 'helpwizard'=>true, 'doNotCopy'=>true, 'maxlength'=>128, 'tl_class'=>'clr'],
            'save_callback' => [[SaveListener::class, '__invoke']],
            'sql'			=> "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        );

        $GLOBALS['TL_LANG'][$table]['permalink'] = $GLOBALS['TL_LANG']['DCA']['permalink'];
        $GLOBALS['TL_LANG'][$table]['permalink_legend'] = $GLOBALS['TL_LANG']['DCA']['permalink_legend'];
    }

    /**
     * Add permalink default pattern and remove useAutoItem and folderUrl
     */
    protected function addPermalinkSettings()
    {
        $db = Database::getInstance();
        $palette = ';{permalink_legend}';

        /** @var AbstractPermalinkHandler $handler */
        foreach($this->permalink->getSupportedContexts() as $context) {
            if ($db->tableExists($this->permalink->getTable($context))) {
                $GLOBALS['TL_DCA']['tl_settings']['fields'][$context . 'Permalink'] = array
                (
                    'label'			=> &$GLOBALS['TL_LANG']['tl_settings'][$context . 'Permalink'],
                    'default'		=> $this->permalink->getDefault($this->permalink->getTable($context)),
                    'inputType'		=> 'text',
                    'eval'			=> ['tl_class' => 'w50'],
                    'save_callback' => [[SaveListener::class, '__invoke']]
                );

                $palette .= ',' . $context . 'Permalink';
            }
        }

        $pattern = ['/,folderUrl/', '/({frontend_legend}.*?);/'];
        $replace = ['', '$1' . $palette . ';'];

        $GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);
    }
}
