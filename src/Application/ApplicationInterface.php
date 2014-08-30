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