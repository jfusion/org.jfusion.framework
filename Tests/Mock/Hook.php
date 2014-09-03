<?php namespace JFusion\Tests\Mock;
/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 21-08-14
 * Time: 21:31
 */

use JFusion\Api\PlatformInterface;
use JFusion\Application\ApplicationInterface;
use JFusion\Event\LanguageInterface;
use JFusion\Installer\PluginInterface;

use Joomla\Event\Event;

/**
 * Class Hook
 * @package JFusion\Tests\Mock
 */
class Hook implements LanguageInterface, ApplicationInterface, PluginInterface , PlatformInterface {
	/**
	 * Enqueue a system message.
	 *
	 * @param Event $event
	 */
	public function onApplicationEnqueueMessage($event)
	{
		// TODO: Implement onApplicationEnqueueMessage() method.
	}

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 */
	public function onApplicationLoadScriptLanguage($event)
	{
		// TODO: Implement onApplicationLoadScriptLanguage() method.
	}

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 */
	public function onApplicationGetUser($event)
	{
		// TODO: Implement onApplicationGetUser() method.
	}

	/**
	 * Loads a language file for framework
	 *
	 * @param Event $event
	 */
	function onLanguageLoadFramework($event)
	{
		// TODO: Implement onLanguageLoadFramework() method.
	}

	/**
	 * Loads a language file for plugin
	 *
	 * @param Event $event
	 */
	function onLanguageLoadPlugin($event)
	{
		// TODO: Implement onLanguageLoadPlugin() method.
	}

	/**
	 * used for platform login
	 *
	 * @param   Event $event
	 */
	function onPlatformLogin($event)
	{
		// TODO: Implement onPlatformLogin() method.
	}

	/**
	 * used for platform logout
	 *
	 * @param   Event $event
	 */
	function onPlatformLogout($event)
	{
		// TODO: Implement onPlatformLogout() method.
	}

	/**
	 * used for platform delete user
	 *
	 * @param   Event $event
	 */
	function onPlatformUserDelete($event)
	{
		// TODO: Implement onPlatformUserDelete() method.
	}

	/**
	 * used for platform user register
	 *
	 * @param   Event $event
	 */
	function onPlatformUserRegister($event)
	{
		// TODO: Implement onPlatformUserRegister() method.
	}

	/**
	 * used for platform user update
	 *
	 * @param   Event $event
	 */
	function onPlatformUserUpdate($event)
	{
		// TODO: Implement onPlatformUserUpdate() method.
	}

	/**
	 * used for platform route url
	 *
	 * @param   Event $event
	 */
	function onPlatformRoute($event)
	{
		// TODO: Implement onPlatformRoute() method.
	}

	/**
	 * @param Event $event
	 */
	function onInstallerPluginUninstall($event)
	{
		// TODO: Implement onInstallerPluginUninstall() method.
	}
}