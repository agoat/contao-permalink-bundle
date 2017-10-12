<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PermalinkBundle\Controller;

use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Main front end controller.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class EventController implements ControllerInterface
{

	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_calendar_events';
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	public function run($source, Request $request)
	{
		$objEvent = \CalendarEventsModel::findByPk($source);

		// Throw a 404 error if the event could not be found
		if (null === $objEvent)
		{
			throw new PageNotFoundException('Event not found: ' . $request->getUri());
		}

		// Set the event id as get attribute
		\Input::setGet('events', $objEvent->id, true);

		$objCalendar = \CalendarModel::FindByPk($objEvent->pid);
		$objPage = \PageModel::findByPk($objCalendar->jumpTo);
		
		// Render the corresponding page from the calender setting
		$frontendIndex = new FrontendIndex();
		return $frontendIndex->renderPage($objPage);
	}
}