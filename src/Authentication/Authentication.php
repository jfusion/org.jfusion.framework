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
	 * @param   array  $credentials  Array holding the user credentials.
	 * @param   array  $options      Array holding user options.
	 *
	 * @return  Response  Response object with status variable filled in for last plugin or first successful plugin.
	 *
	 * @see     AuthenticationResponse
	 * @since   11.1
	 */
	public function authenticate($credentials, $options = array())
	{
		// Create authentication response
		$response = new Response();

		$debugger = Debugger::getInstance('jfusion-authentication');
		$debugger->set(null, array());

		$db = Factory::getDBO();
		//get the JFusion master
		$master = Framework::getMaster();
		if (!empty($master)) {
			$userinfo = new Userinfo(null);
			if (isset($credentials['username'])) {
				$userinfo->username = $credentials['username'];
			}
			if (isset($credentials['email'])) {
				$userinfo->email = $credentials['email'];
			}

			if (isset($options['skip_password_check']) && $options['skip_password_check'] === true) {
				$debugger->addDebug(Text::_('SKIPPED') . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK'));
				$response->status = Authentication::STATUS_SUCCESS;
				$response->error_message = '';
				$response->userinfo = $userinfo;
			} elseif (empty($credentials['password'])) {
				$response->status = Authentication::STATUS_FAILURE;
				$response->error_message = Text::_('EMPTY_PASSWORD_NO_ALLOWED');
			} else {
				$JFusionMaster = Factory::getUser($master->name);
				try {
					$userinfo = $JFusionMaster->getUser($userinfo);
				} catch (Exception $e) {
					$userinfo = null;
				}
				//check if a user was found
				if ($userinfo instanceof Userinfo) {
					//apply the clear text password to the user object
					$userinfo->password_clear = $credentials['password'];
					//check the master plugin for a valid password
					$model = Factory::getAuth($master->name);

					try {
						$check = $model->checkPassword($userinfo);
					} catch (Exception $e) {
						Framework::raise(LogLevel::ERROR, $e, $model->getJname());
						$check = false;
					}
					if ($check) {
						//found a match
						$debugger->addDebug($master->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' . Text::_('SUCCESS'));
						$response->status = Authentication::STATUS_SUCCESS;
						$response->error_message = '';
						$response->userinfo = $userinfo;
					} else {
						$testcrypt = $model->generateEncryptedPassword($userinfo);
						if (isset($options['show_unsensored'])) {
							$debugger->addDebug($master->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' . $testcrypt . ' vs ' . $userinfo->password);
						} else {
							$debugger->addDebug($master->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' .  substr($testcrypt, 0, 6) . '******** vs ' . substr($userinfo->password, 0, 6) . '********');
						}

						$slaves = Framework::getSlaves();

						//loop through the different models
						foreach ($slaves as $slave) {
							try {
								if ($slave->check_encryption == 1) {
									//Generate an encrypted password for comparison
									$model = Factory::getAuth($slave->name);
									$JFusionSlave = Factory::getUser($slave->name);
									$slaveuserinfo = $JFusionSlave->getUser($userinfo);
									// add in the clear password to be able to generate the hash
									if ($slaveuserinfo instanceof Userinfo) {
										$slaveuserinfo->password_clear = $userinfo->password_clear;
										$testcrypt = $model->generateEncryptedPassword($slaveuserinfo);
										$check = $model->checkPassword($slaveuserinfo);
									} else {
										$testcrypt = $model->generateEncryptedPassword($userinfo);
										$check = $model->checkPassword($userinfo);
									}

									if ($check) {
										//found a match
										$debugger->addDebug($slave->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' . Text::_('SUCCESS'));
										$response->status = Authentication::STATUS_SUCCESS;
										$response->error_message = '';
										$response->userinfo = $userinfo;
										//update the password format to what the master expects
										$JFusionMaster = Factory::getUser($master->name);
										//make sure that the password_clear is not already hashed which may be the case for some dual login plugins

										$JFusionMaster->resetDebugger();

										$JFusionMaster->doUpdatePassword($userinfo, $userinfo);

										$status = $JFusionMaster->debugger->get();
										if (!empty($status[LogLevel::ERROR])) {
											foreach($status[LogLevel::ERROR] as $error) {
												$debugger->addDebug($slave->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR') . ': ' . $error);
											}
											Framework::raise(LogLevel::ERROR, $status[LogLevel::ERROR], $master->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('UPDATE'));
										} else {
											$debugger->addDebug($slave->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('UPDATE') . ' ' . Text::_('SUCCESS'));
										}
									} else {
										if (isset($options['show_unsensored'])) {
											$debugger->addDebug($slave->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' .  $testcrypt . ' vs ' . $userinfo->password);
										} else {
											$debugger->addDebug($slave->name . ' ' . Text::_('PASSWORD') . ' ' . Text::_('ENCRYPTION') . ' ' . Text::_('CHECK') . ': ' .  substr($testcrypt, 0, 6) . '******** vs ' . substr($userinfo->password, 0, 6) . '********');
										}
									}
								}
							} catch (Exception $e) {
								Framework::raise(LogLevel::ERROR, $e);
							}
						}
					}
				} else {
					$response->error_message = Text::_('USER_NOT_EXIST');
					$debugger->addDebug(Text::_('USER_NOT_EXIST'));
				}
			}
		} else {
			$response->status = Authentication::STATUS_UNKNOWN;
			$response->error_message = Text::_('JOOMLA_AUTH_PLUGIN_USED_NO_MASTER');
			$debugger->addDebug(Text::_('JOOMLA_AUTH_PLUGIN_USED_NO_MASTER'));
		}
		$response->debugger = $debugger;

		return $response;
	}
}