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
	 */
	public function onApplicationEnqueueMessage($event);

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 */
	public function onApplicationLoadScriptLanguage($event);

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 */
	public function onApplicationGetUser($event);
}