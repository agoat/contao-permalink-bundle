<?php


namespace Agoat\PermalinkBundle\EventListener\DataContainer;


use Agoat\PermalinkBundle\Permalink\Permalink;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Generate a permalink
 *
 * @internal We can not use the Callback annotation here as the listener is added dynamically (depending on existing permalink handlers)
 */
class OnSubmitListener
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

    public function __invoke(DataContainer $dc)
    {
        try {
            $this->permalink->generate($dc);

        } catch (ResponseException $e) {
            throw $e;

        } catch (\Exception $e) {
            /** @var AttributeBagInterface $sessionBag */
            $sessionBag = $this->session->getBag('contao_backend');

            // Save permalink error to session instead of throwing an exception (will be handled later in the permlinkWizard)
            $sessionBag->set('permalink_error', $e->getMessage());
            return;
        }

        $url =  $this->permalink->getUrl($dc);

        if(null !== $url) {
            $this->saveAlias($url->getPath(), $dc->id, $dc->table);
        }
    }
}
