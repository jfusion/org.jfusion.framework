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
	 * Loads a language file for framework
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	function onLanguageLoadFramework($event)
	{
		// TODO: Implement onLanguageLoadFramework() method.
	}

	/**
	 * Loads a language file for plugin
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	function onLanguageLoadPlugin($event)
	{
		// TODO: Implement onLanguageLoadPlugin() method.
	}

	/**
	 * @param Event $event
	 *
	 * @return  Event
	 */
	function onInstallerPluginUninstall($event)
	{
		// TODO: Implement onInstallerPluginUninstall() method.
	}

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
	function onApplicationRedirect($event)
	{
		// TODO: Implement onApplicationRedirect() method.
	}

	/**
	 * Logout authentication function.
	 *
	 * Passed the current user information to the onUserLogout event and reverts the current
	 * session record back to 'anonymous' parameters.
	 * If any of the authentication plugins did not successfully complete
	 * the logout routine then the whole method fails. Any errors raised
	 * should be done in the plugin as this provides the ability to give
	 * much more information about why the routine may have failed.
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	function onApplicationLogout($event)
	{
		// TODO: Implement onApplicationLogout() method.
	}

	/**
	 * Login authentication function.
	 *
	 * Username and encoded password are passed the onUserLogin event which
	 * is responsible for the user validation. A successful validation updates
	 * the current session record with the user's details.
	 *
	 * Username and encoded password are sent as credentials (along with other
	 * possibilities) to each observer (authentication plugin) for user
	 * validation.  Successful validation will update the current session with
	 * the user details.
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	public function onApplicationLogin($event)
	{
		// TODO: Implement onApplicationLogin() method.
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	public function onApplicationEnqueueMessage($event)
	{
		// TODO: Implement onApplicationEnqueueMessage() method.
	}

	/**
	 * get default url
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	public function onApplicationGetDefaultAvatar($event)
	{
		// TODO: Implement onApplicationGetDefaultAvatar() method.
	}

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	public function onApplicationLoadScriptLanguage($event)
	{
		// TODO: Implement onApplicationLoadScriptLanguage() method.
	}

	/**
	 * Load Script language
	 *
	 * @param Event $event
	 *
	 * @return  Event
	 */
	public function onApplicationGetUser($event)
	{
		// TODO: Implement onApplicationGetUser() method.
	}

	/**
	 * used for platform login
	 *
	 * @param   Event $event
	 *
	 * @return  Event
	 */
	function onPlatformLogin($event)
	{
		// TODO: Implement onPlatformLogin() method.
	}

	/**
	 * used for platform logout
	 *
	 * @param   Event $event
	 *
	 * @return  Event
	 */
	function onPlatformLogout($event)
	{
		// TODO: Implement onPlatformLogout() method.
	}

	/**
	 * used for platform delete user
	 *
	 * @param   Event $event
	 *
	 * @return  Event
	 */
	function onPlatformUserDelete($event)
	{
		// TODO: Implement onPlatformUserDelete() method.
	}

	/**
	 * used for platform user register
	 *
	 * @param   Event $event
	 *
	 * @return  Event
	 */
	function onPlatformUserRegister($event)
	{
		// TODO: Implement onPlatformUserRegister() method.
	}

	/**
	 * used for platform user update
	 *
	 * @param   Event $event
	 *
	 * @return  Event
	 */
	function onPlatformUserUpdate($event)
	{
		// TODO: Implement onPlatformUserUpdate() method.
	}

	/**
	 * used for platform route url
	 *
	 * @param   Event $event
	 *
	 * @return  Event
	 */
	function onPlatformRoute($event)
	{
		// TODO: Implement onPlatformRoute() method.
	}
}