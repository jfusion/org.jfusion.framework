<?php namespace JFusion\Authentication;
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use JFusion\Debugger\Debugger;
use JFusion\Factory;
use JFusion\Framework;
use JFusion\User\User;
use JFusion\User\Userinfo;

use Joomla\Language\Text;

use Psr\Log\LogLevel;

use \Exception;

/**
 * Authentication class, provides an interface for the Joomla authentication system
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       11.1
 */
class Authentication
{
	// Shared success status
	/**
	 * This is the status code returned when the authentication is success (permit login)
	 * @const  STATUS_SUCCESS successful response
	 * @since  11.2
	 */
	const STATUS_SUCCESS = 1;

	// These are for authentication purposes (username and password is valid)
	/**
	 * Status to indicate cancellation of authentication (unused)
	 * @const  STATUS_CANCEL cancelled request (unused)
	 * @since  11.2
	 */
	const STATUS_CANCEL = 2;

	/**
	 * This is the status code returned when the authentication failed (prevent login if no success)
	 * @const  STATUS_FAILURE failed request
	 * @since  11.2
	 */
	const STATUS_FAILURE = 4;

	// These are for authorisation purposes (can the user login)
	/**
	 * This is the status code returned when the account has expired (prevent login)
	 * @const  STATUS_EXPIRED an expired account (will prevent login)
	 * @since  11.2
	 */
	const STATUS_EXPIRED = 8;

	/**
	 * This is the status code returned when the account has been denied (prevent login)
	 * @const  STATUS_DENIED denied request (will prevent login)
	 * @since  11.2
	 */
	const STATUS_DENIED = 16;

	/**
	 * This is the status code returned when the account doesn't exist (not an error)
	 * @const  STATUS_UNKNOWN unknown account (won't permit or prevent login)
	 * @since  11.2
	 */
	const STATUS_UNKNOWN = 32;

	/**
	 * An array of Observer objects to notify
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $observers = array();

	/**
	 * A multi dimensional array of [function][] = key for observers
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $methods = array();

	/**
	 * @var    Authentication  Authentication instances container.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * @since   11.1
	 */
	public function __construct()
	{
	}

	/**
	 * Returns the global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  Authentication  The global JAuthentication object
	 *
	 * @since   11.1
	 */
	public static function getInstance()
	{
		if (empty(self::$instance))
		{
			self::$instance = new Authentication;
		}

		return self::$instance;
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all observing
	 * objects to run their respective authentication routines.
	 *
	 * @param   Userinfo  $userinfo  Array holding the user credentials.
	 * @param   array  $options      Array holding user options.
	 *
	 * @return  Response  Response object with status variable filled in for last plugin or first successful plugin.
	 *
	 * @see     AuthenticationResponse
	 * @since   11.1
	 */
	public function authenticate(Userinfo $userinfo, $options = array())
	{
		// Create authentication response
		$response = new Response();
		$debugger = new Debugger();

		try {
			$db = Factory::getDBO();

			$plugins = Factory::getPlugins();
			if (!empty($plugins)) {
				$userinfo = User::search($userinfo);
				if ($userinfo instanceof Userinfo) {
					if (isset($options['skip_password_check']) && $options['skip_password_check'] === true) {
						$debugger->addDebug(Text::_('SKIPPED') . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK'));
						$response->status = Authentication::STATUS_SUCCESS;
						$response->error_message = '';
						$response->userinfo = $userinfo;
					} elseif (empty($userinfo->password_clear)) {
						throw new \RuntimeException(Text::_('EMPTY_PASSWORD_NO_ALLOWED'));
					} else {
						$authUserinfo = null;
						$check = false;
						foreach ($plugins as $key => $plugin) {
							if ($key == 0 || $plugin->check_encryption == 1) {
								$userPlugin = Factory::getUser($plugin->name);

								try {
									$authUserinfo = $userPlugin->getUser($userinfo);

									if ($authUserinfo instanceof Userinfo) {
										$authUserinfo->password_clear = $userinfo->password_clear;
										$authPlugin = Factory::getAuth($plugin->name);

										$check = $authPlugin->checkPassword($authUserinfo);

										if ($check === true) {
											//found a match
											$debugger->addDebug($plugin->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' . Text::_('SUCCESS'));
											$response->status = Authentication::STATUS_SUCCESS;
											$response->error_message = '';
											$response->userinfo = $authUserinfo;

											break;
										} else {
											$testcrypt = $authPlugin->generateEncryptedPassword($authUserinfo);

											if (isset($options['mask'])) {
												$debugger->addDebug($plugin->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' .  substr($testcrypt, 0, 6) . '******** vs ' . substr($userinfo->password, 0, 6) . '********');
											} else {
												$debugger->addDebug($plugin->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' . $testcrypt . ' vs ' . $userinfo->password);
											}
										}
									}
								} catch (Exception $e) {
									Framework::raise(LogLevel::ERROR, $e, $plugin->name);
									$check = false;
									$authUserinfo = null;
								}
							}
						}

						if ($check) {
							foreach ($plugins as $key => $plugin) {
								if ($plugin->name != $authUserinfo->getJname()) {
									$userPlugin = Factory::getUser($plugin->name);

									$authUserinfo2 = $userPlugin->getUser($userinfo);

									if ($authUserinfo2 instanceof Userinfo) {
										$authUserinfo2->password_clear = $userinfo->password_clear;

										$authPlugin = Factory::getAuth($plugin->name);
										$check = $authPlugin->checkPassword($authUserinfo2);
										if ($check !== true) {
											$userPlugin->resetDebugger();

											$userPlugin->doUpdatePassword($userinfo, $authUserinfo2);

											$status = $userPlugin->debugger->get();
											if (!empty($status[LogLevel::ERROR])) {
												foreach($status[LogLevel::ERROR] as $error) {
													$debugger->addDebug($plugin->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR') . ': ' . $error);
												}
												Framework::raise(LogLevel::ERROR, $status[LogLevel::ERROR], $plugin->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('UPDATE'));
											}
										}
									} else {
										/**
										 * TODO: CREATE USER?? or leave it for login?
										 */
									}
								}
							}
						}
					}
				} else {
					throw new \RuntimeException(Text::_('USER_NOT_EXIST'));
				}
			} else {
				throw new \RuntimeException(Text::_('JOOMLA_AUTH_PLUGIN_USED_NO_MASTER'));
			}
		} catch (Exception $e) {
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = $e->getMessage();
			$debugger->addDebug($e->getMessage());
		}
		$response->debugger = $debugger;
		return $response;
	}
}