<?php namespace JFusion;
use Joomla\Event\Event;

/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 18-03-14
 * Time: 14:14
 */
interface FrameworkInterface
{
	/**
	 * Loads a language file for framework
	 *
	 * @param Event $event
	 */
	function onFrameworkLoadLanguage($event);
}