<?php namespace JFusion\Session;

use Joomla\Event\Event;

/**
 * Interface ISession
 *
 * @package JFusion\Event
 */
interface SessionInterface
{
	/**
	 * Close the session
	 *
	 * @param   Event   $event
	 *
	 * @return  Event
	 */
	function onSessionClose($event);

	/**
	 * Restart an expired or locked session.
	 *
	 * @param   Event   $event
	 *
	 * @return  Event
	 */
	public function onSessionRestart($event);
}