<?php namespace JFusion\Application;

use Joomla\Event\Event;

/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 18-03-14
 * Time: 14:14
 */
interface ApplicationInterface
{
	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * or "303 See Other" code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	function onApplicationRedirect($event);

	/**
	 * Enqueue a system message.
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	public function onApplicationEnqueueMessage($event);

	/**
	 * get default url
	 *
	 * @param Event $event
	 * @return  Event
	 */
	public function onApplicationGetDefaultAvatar($event);

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 * @return  Event
	 */
	public function onApplicationLoadScriptLanguage($event);

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 * @return  Event
	 */
	public function onApplicationGetUser($event);
}