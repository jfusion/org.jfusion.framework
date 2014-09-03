<?php namespace JFusion\Installer;

use Joomla\Event\Event;

/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 18-03-14
 * Time: 14:14
 */
interface InstallerPluginInterface
{
	/**
	 * @param Event $event
	 */
	function onInstallerPluginUninstall($event);
}