<?php namespace JFusion\Plugin;
use Joomla\Event\Event;
/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 18-03-14
 * Time: 14:14
 */
interface PluginInterface
{
	/**
	 * Loads a language file for plugin
	 *
	 * @param Event $event
	 */
	function onPluginLoadLanguage($event);
}