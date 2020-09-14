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

use Agoat\PermalinkBundle\Model\PermalinkModel;
use Agoat\PermalinkBundle\Permalink\Permalink;
use Contao\CoreBundle\Controller\AbstractController;
use Contao\FrontendIndex;
use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\NoRootPageFoundException;
use Contao\Input;
use Contao\PageModel;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Handles the permalink routes
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class PermalinkController extends AbstractController
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

    /**
     * Fetch and run the responsible contoller form the database
     *
     * @param string $path
     * @param Request $request
     * @param ControllerLocator $controllerChain
     *
     * @return Response
     */
    public function guidAction($path, Request $request)
    {
        // First try to find an url entry directly
        $permalink = PermalinkModel::findByGuid($request->getHost() . '/' . $path);

        // Then try to find a parent url entry
        while (null === $permalink && strpos($path, '/') !== false) {
            $arrFragments[] = basename($path);
            $path = dirname($path);

            $permalink = PermalinkModel::findByGuid($request->getHost() . '/' . $path);
        }

        if (null === $permalink) {
            throw new PageNotFoundException('Page not found: ' . $request->getUri());
        }

        // Save the fragments for modules
        if (!empty($arrFragments)) {
            $arrFragments = array_reverse($arrFragments);

            $legacy = in_array($arrFragments[0], $GLOBALS['TL_AUTO_ITEM']);

            // Save fragments as get paramters
            foreach ($arrFragments as $key=>$value) {
                Input::setGet($key, $value, !$legacy);
            }

            // Save as key value pairs (legacy support)
            if ($legacy) {
                // Add the fragments to the $_GET array (legacy support)
                for ($i=0, $c=count($arrFragments); $i<$c; $i+=2) {
                    // Skip key value pairs if the key is empty (see #4702)
                    if ($arrFragments[$i] == '') {
                        continue;
                    }

                    // Skip duplicate parameter (duplicate content) (see #4277)
                    if (isset($_GET[$arrFragments[$i]])) {
                        continue;
                    }

                    Input::setGet(urldecode($arrFragments[$i]), urldecode($arrFragments[$i+1]), true);
                }
            }
        }

        return $this->renderPage($permalink, $request);
    }

	/**
	 * Fetch a matching lanugage page and redirect
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function rootAction(Request $request)
	{
		// First try to find an url entry directly (pages with the {{index}} insert tag)
		$permalink = PermalinkModel::findByGuid($request->getHost());

		// Then try to find a root page and redirect to the first regular page
		if (null === $permalink || null === \PageModel::findPublishedById($permalink->source))
		{
            $rootpages = PageModel::findBy(['type=?', '(dns=? OR dns=\'\')', 'published=\'1\''], ['root', $request->getHost()], ['order' => 'dns DESC']);

            if (null === $rootpages) {
                throw new NoRootPageFoundException('No rootpage found');
            }

            $availableLanguages = $rootpages->fetchEach('language');
            $language = $request->getPreferredLanguage($availableLanguages);

            if (null === $language) {
                $fallbackpage = PageModel::findBy(['type=?', '(dns=? OR dns=\'\')', 'fallback=?', 'published=\'1\''], ['root', $request->getHost(), 1], ['limit'=>1, 'order'=>'dns DESC']);

                if (null === $fallbackpage)
                {
                    throw new NoRootPageFoundException('No rootpage found');
                }

                $source = $fallbackpage->id;

            } else {
                $source = array_flip(array_reverse($availableLanguages, true))[$language];
            }

			$objPage = PageModel::findFirstPublishedByPid($source);

			if (null === $objPage) {
				throw new NoRootPageFoundException('No regular page found');
			}

			return $this->redirectToRoute('contao_permalink', array('path' => $objPage->alias));
		}

        return $this->renderPage($permalink, $request);
	}

    /**
     * Render the page
     *
     * @param PermalinkModel $permalink
     * @param Request $request
     * @return Response
     */
	private function renderPage(PermalinkModel $permalink, Request $request)
    {
        if (! $this->permalink->supportsContext($permalink->context)) {
            throw new PageNotFoundException('Page not found: ' . $request->getUri());
        }

        $frontendIndex = new FrontendIndex();
        return $frontendIndex->renderPage($this->permalink->findPage($permalink->context, $permalink->source, $request));
    }
}
