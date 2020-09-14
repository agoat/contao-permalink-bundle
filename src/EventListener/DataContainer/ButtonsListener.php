<?php


namespace Agoat\PermalinkBundle\EventListener\DataContainer;

use Agoat\PermalinkBundle\Permalink\Permalink;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Contao\Versions;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Automatically generate permalinks for all selected entities
 *
 * @internal We can not use the Callback annotation here as the listener is added dynamically (depending on existing permalink handlers)
 */
class ButtonsListener
{
    use SaveAliasTrait;

    /** @var Permalink */
    private $permalink;

    /** @var SessionInterface */
    private $session;

    public function __construct(Permalink $permalink, SessionInterface $session)
    {
        $this->permalink = $permalink;
        $this->session = $session;
    }

    public function __invoke(array $arrButtons, DataContainer $dc)
    {
        // Generate/update the permalinks
        if (Input::post('FORM_SUBMIT') === 'tl_select' && isset($_POST['permalink']))
        {
            $session = $this->session->all();
            $ids = $session['CURRENT']['IDS'];

            $db = Database::getInstance();

            foreach ($ids as $id) {
                $dc->id = $id;

                $db->prepare("UPDATE $dc->table SET permalink=? WHERE id='$id' and permalink=''")->execute($GLOBALS['TL_DCA'][$dc->table]['fields']['permalink']['default']);

                try {
                    $this->permalink->generate($dc);

                } catch (ResponseException $e) {
                    throw $e;

                } catch (\Exception $e) {}

                $url =  $this->permalink->getUrl($dc);

                $alias = $db->execute("SELECT alias FROM $dc->table WHERE id='$id'");

                if(null !== $url && null !== $alias && $url->getPath() != $alias->alias) {
                    $objVersions = new Versions($dc->table, $id);
                    $objVersions->initialize();

                    $this->saveAlias($url->getPath(), $id, $dc->table);

                    $objVersions->create();
                }
            }

            Controller::redirect(System::getReferer());
        }

        // Add the button
        $arrButtons['permalink'] = '<button type="submit" name="permalink" id="permalink" class="tl_submit" accesskey="p">'.$GLOBALS['TL_LANG']['MSC']['permalinkSelected'].'</button> ';

        return $arrButtons;
    }

}
