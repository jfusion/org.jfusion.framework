<?php namespace JFusion\Router;

use Joomla\Event\Event;

/**
 * Interface IRouter
 *
 * @package JFusion\Event
 */
interface RouterInterface
{
	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   Event   $event
	 *
	 * @return  Event
	 */
	function  onRouterBuild($event);
}