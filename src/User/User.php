<?php namespace JFusion\User;
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use JFusion\Debugger\Debugger;
use JFusion\Factory;
use JFusion\Framework;

use Joomla\Language\Text;

use Psr\Log\LogLevel;

use RuntimeException;
use Exception;

/**
 * Joomla! CMS Application class
 *
 * @package     Joomla.Libraries
 * @subpackage  Application
 * @since       3.2
 */
class User
{
	/**
	 * @var    User  The application instance.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 * @var Debugger $debugger
	 */
	private $debugger;

	/**
	 *
	 */
	function __construct()
	{
		$this->debugger = new Debugger();
	}

	/**
	 * Returns a reference to the global JApplicationCms object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $web = JApplicationCms::getInstance();
	 *
	 * @return  User
	 */
	public static function getInstance()
	{
		if (!static::$instance)
		{
			static::$instance = new User();
		}
		return static::$instance;
	}

	/**
	 * @return Debugger
	 */
	public function getDebugger()
	{
		return $this->debugger;
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
	 * @param   Userinfo  $userinfo
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function login(Userinfo $userinfo, $options = array())
	{
		$success = 0;
		$this->debugger->set(null, array());
		$this->debugger->set('init', array());
		try {
			global $JFusionActive, $JFusionLoginCheckActive, $JFusionActivePlugin;

			$JFusionActive = true;

			//php 5.3 does not allow plugins to contain pass by references
			//use a global for the login checker instead

			if (!isset($options['skipplugin'])) {
				$options['skipplugin'] = array();
			}
			if (!isset($options['overwrite'])) {
				$options['overwrite'] = 0;
			}
			if (!isset($options['mask'])) {
				$options['mask'] = true;
			}

			if (!empty($JFusionActivePlugin)) {
				$options['skipplugin'][] = $JFusionActivePlugin;
			}

			//allow for the detection of external mods to exclude jfusion plugins
			if (isset($options['nodeid']) && !empty($options['nodeid'])) {
				$JFusionActivePlugin = $options['nodeid'];
				$options['skipplugin'][] = $options['nodeid'];
			}

			$authUserinfo = User::search($userinfo, true);

			if ($authUserinfo instanceof Userinfo) {
				$authUserinfo->password_clear = $userinfo->password_clear;

				$plugins = Factory::getPlugins();
				foreach ($plugins as $plugin) {
					if (!in_array($plugin->name, $options['skipplugin'])) {
						$userPlugin = Factory::getUser($plugin->name);

						$autoregister = $userPlugin->params->get('autoregister', 0);

						try {
							$userinfo = $userPlugin->getUser($authUserinfo);
						} catch (Exception $e) {
							$userinfo = null;
						}

						if (!$userinfo instanceof Userinfo) {
							if ($autoregister == 1) {
								try {
									$this->debugger->add('init', $plugin->name . ' ' .Text::_('CREATING_USER'));
									//try to create a Master user

									$userPlugin->resetDebugger();
									if ($userPlugin->validateUser($authUserinfo)) {
										$userinfo = $userPlugin->doCreateUser($authUserinfo);
										$this->debugger->add('init', Text::_('MASTER') . ' ' . Text::_('USER') . ' ' . Text::_('CREATE') . ' ' . Text::_('SUCCESS'));
									}
								} catch (Exception $e) {
									Framework::raise(LogLevel::ERROR, Text::_('USER') . ' ' . Text::_('CREATE') . ' ' . Text::_('ERROR') . ' ' . $e->getMessage(), $plugin->name);
									$this->debugger->add($plugin->name . ' ' . Text::_('USER') . ' ' . Text::_('CREATE') . ' ' . Text::_('ERROR'), $e->getMessage());
								}
							}
						} else {
							try {
								$userPlugin->resetDebugger();
								if ($userPlugin->validateUser($userinfo)) {
									$userinfo = $userPlugin->updateUser($authUserinfo, $options['overwrite']);

									$debug = $userPlugin->debugger->get();
									if (!$userinfo instanceof UserInfo) {
										//make sure the userinfo is available
										$userinfo = $userPlugin->getUser($authUserinfo);
									}
									if (!empty($debug[LogLevel::ERROR])) {
										$this->debugger->set($plugin->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR'), $debug[LogLevel::ERROR]);
									}
									if (!empty($debug[LogLevel::DEBUG])) {
										$this->debugger->set($plugin->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('DEBUG'), $debug[LogLevel::DEBUG]);
									}

									if ($userinfo instanceof UserInfo) {
										if ($options['mask']) {
											$details = $userinfo->getAnonymizeed();
										} else {
											$details = $userinfo->toObject();
										}
									} else {
										$details = null;
									}
									$this->debugger->set($plugin->name . ' ' . Text::_('USERINFO'), $details);
								}
							} catch (Exception $e) {
								Framework::raise(LogLevel::ERROR, $e, $plugin->name);
								$this->debugger->add($plugin->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR'), $e->getMessage());
							}
						}
					}
				}
				if ($authUserinfo->canLogin()) {
					foreach ($plugins as $plugin) {
						if (!in_array($plugin->name, $options['skipplugin']) && $plugin->dual_login == 1) {
							$userPlugin = Factory::getUser($plugin->name);

							try {
								$userinfo = $userPlugin->getUser($authUserinfo);
							} catch (Exception $e) {
								$userinfo = null;
							}

							if ($userinfo instanceof UserInfo) {
								$userinfo->password_clear = $authUserinfo->password_clear;
								try {
									$session = $userPlugin->createSession($userinfo, $options);

									if (!empty($session[LogLevel::ERROR])) {
										$this->debugger->set($plugin->name . ' ' . Text::_('SESSION') . ' ' . Text::_('ERROR'), $session[LogLevel::ERROR]);
										Framework::raise(LogLevel::ERROR, $session[LogLevel::ERROR], $plugin->name . ': ' . Text::_('SESSION') . ' ' . Text::_('CREATE'));
									}
									if (!empty($session[LogLevel::DEBUG])) {
										$this->debugger->set($plugin->name . ' ' . Text::_('SESSION') . ' ' . Text::_('DEBUG'), $session[LogLevel::DEBUG]);
										//report the error back
									}
									$success = 1;
								} catch (Exception $e) {
									$this->debugger->set($plugin->name . ' ' . Text::_('SESSION') . ' ' . Text::_('ERROR'), $e->getMessage());
									Framework::raise(LogLevel::ERROR, $e, $plugin->name . ': ' . Text::_('SESSION') . ' ' . Text::_('CREATE'));
								}
							}
						}
					}
				} else if ($authUserinfo->block) {
					throw new RuntimeException(Text::_('FUSION_BLOCKED_USER'));
				} else {
					throw new RuntimeException(Text::_('FUSION_INACTIVE_USER'));
				}
			} else {
				//return an error
				$this->debugger->add('init', Text::_('COULD_NOT_FIND_USER'));
				throw new RuntimeException(Text::_('COULD_NOT_FIND_USER'));
			}
		} catch (Exception $e) {
			$success = 0;
			Framework::raise(LogLevel::ERROR, $e);
			$this->debugger->addError($e->getMessage());
		}
	    return ($success === 1);
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
	 * @param   Userinfo $userinfo   The user to load - Can be an integer or string - If string, it is converted to ID automatically
	 * @param   array       $options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	public function logout(Userinfo $userinfo, $options = array())
	{
		//initialise some vars
		global $JFusionActive;
		$JFusionActive = true;

		if (!isset($options['skipplugin'])) {
			$options['skipplugin'] = array();
		}
		if (!isset($options['mask'])) {
			$options['mask'] = true;
		}

		//allow for the detection of external mods to exclude jfusion plugins
		global $JFusionActivePlugin;

		if (!empty($JFusionActivePlugin)) {
			$options['skipplugin'][] = $JFusionActivePlugin;
		}

		if (isset($options['nodeid']) && !empty($options['nodeid'])) {
			$JFusionActivePlugin = $options['nodeid'];
			$options['skipplugin'][] = $options['nodeid'];
		}

		//prevent any output by the plugins (this could prevent cookies from being passed to the header)
		//logout from the JFusion plugins if done through frontend

		$plugins = Factory::getPlugins();
		foreach ($plugins as $plugin)
		{
			if (!in_array($plugin->name, $options['skipplugin'])) {
				if ($plugin->dual_login == 1) {
					$userPlugin = Factory::getUser($plugin->name);
					$userlookup = static::search($userinfo);
					$this->debugger->set('userlookup', $userlookup);
					if ($userlookup instanceof Userinfo) {
						$details = null;
						try {
							$pluginuser = $userPlugin->getUser($userlookup);
						} catch (Exception $e) {
							$pluginuser = null;
						}
						if ($pluginuser instanceof Userinfo) {
							if ($options['mask']) {
								$details = $pluginuser->getAnonymizeed();
							} else {
								$details = $pluginuser->toObject();
							}

							try {
								$session = $userPlugin->destroySession($pluginuser, $options);
								if (!empty($session[LogLevel::ERROR])) {
									Framework::raise(LogLevel::ERROR, $session[LogLevel::ERROR], $plugin->name . ': ' . Text::_('SESSION') . ' ' . Text::_('DESTROY'));
								}
								if (!empty($session[LogLevel::DEBUG])) {
									$this->debugger->set($plugin->name . ' logout', $session[LogLevel::DEBUG]);
								}
							} catch (Exception $e) {
								Framework::raise(LogLevel::ERROR, $e, $userPlugin->getJname());
							}
						} else {
							Framework::raise(LogLevel::NOTICE, Text::_('LOGOUT') . ' ' . Text::_('COULD_NOT_FIND_USER'), $plugin->name);
						}
						$this->debugger->set($plugin->name . ' user', $details);
					}
				}
			}
		}
		return true;
	}

	/**
     * Delete user
     *
     * @param Userinfo $userinfo
     *
     * @return boolean
     */
	public function delete(Userinfo $userinfo)
	{
		$result = false;

		$this->debugger->set(null, array());

		//create an array to store the debug info
		$debug_info = array();
		$error_info = array();

		$plugins = Factory::getPlugins();

		$userinfo = User::search($userinfo);
		if ($userinfo instanceof Userinfo) {
			foreach ($plugins as $plugin) {
				$params = Factory::getParams($plugin->name);
				if ($params->get('allow_delete_users', 0)) {
					$userPlugin = Factory::getUser($plugin->name);
					try {
						$pluginUser = $userPlugin->getUser($userinfo);

						if ($pluginUser instanceof Userinfo) {
							$userPlugin->resetDebugger();
							$deleteStatus = $userPlugin->deleteUser($pluginUser);
							$status = $userPlugin->debugger->get();
							if ($deleteStatus) {
								//remove userlookup data
								User::remove($pluginUser);
								$status[LogLevel::DEBUG][] = Text::_('USER_DELETION') . ': ' . $userinfo->userid . ' ( ' . $userinfo->username . ' )';
							}
							if (!empty($status[LogLevel::ERROR])) {
								$error_info[$plugin->name . ' ' . Text::_('USER_DELETION_ERROR') ] = $status[LogLevel::ERROR];
							}
							if (!empty($status[LogLevel::DEBUG])) {
								$debug_info[$plugin->name] = $status[LogLevel::DEBUG];
							}
						} else {
							$debug_info[$plugin->name] = Text::_('NO_USER_DATA_FOUND');
						}
					} catch (Exception $e) {
						$error_info[$plugin->name . ' ' . Text::_('USER_DELETION_ERROR') ] = $e->getMessage();
					}
				} else {
					$debug_info[$plugin->name] = Text::_('DELETE_DISABLED');
				}
			}
			$result = true;
		} else {
			$result = false;
		}
		$this->debugger->set('debug', $debug_info);
		$this->debugger->set('error', $error_info);
		return $result;
	}

	/**
	 * Delete user
	 *
	 * @param Userinfo $userinfo
	 * @param Userinfo $olduserinfo
	 * @param bool     $new
	 *
	 * @return boolean False on Error
	 */
	public function save(Userinfo $userinfo, Userinfo $olduserinfo = null, $new = false)
	{
		$this->debugger->set(null, array());

		//create an array to store the debug info
		$debug_info = array();
		$error_info = array();


		$plugins = Factory::getPlugins();

		if ($userinfo instanceof Userinfo) {
			// Recover the old data of the user
			// This is then used to determine if the username was changed
			$updateUsername = false;

			if ($userinfo->getJname() !== null && $olduserinfo->getJname() !== null) {
				if ($new == false && $userinfo instanceof Userinfo && $olduserinfo instanceof Userinfo) {
					if ($userinfo->getJname() == $olduserinfo->getJname() && $userinfo->username != $olduserinfo->username) {
						$updateUsername = true;
					}
				}
			}
			$exsistingUser = User::search($olduserinfo, true);

			foreach ($plugins as $plugin) {
				try {
					$userPlugin = Factory::getUser($plugin->name);
					if ($userPlugin->validateUser($userinfo)) {
						if ($updateUsername) {
							if ($exsistingUser instanceof Userinfo) {
								$pluginUserinfo = $userPlugin->getUser($exsistingUser);

								if ($pluginUserinfo instanceof Userinfo) {
									try {
										$userPlugin->resetDebugger();
										$userPlugin->updateUsername($userinfo, $pluginUserinfo);
										if (!$userPlugin->debugger->isEmpty('error')) {
											$error_info[$plugin->name . ' ' . Text::_('USERNAME') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR') ] = $userPlugin->debugger->get('error');
										}
										if (!$userPlugin->debugger->isEmpty('debug')) {
											$debug_info[$plugin->name . ' ' . Text::_('USERNAME') . ' ' . Text::_('UPDATE') . ' ' . Text::_('DEBUG') ] = $userPlugin->debugger->get('debug');
										}
									} catch (Exception $e) {
										$status[LogLevel::ERROR][] = Text::_('USERNAME_UPDATE_ERROR') . ': ' . $e->getMessage();
									}
								} else {
									$error_info[$plugin->name] = Text::_('NO_USER_DATA_FOUND');
								}
							}
						}
						//run the update user to ensure any other userinfo is updated as well
						$userPlugin->resetDebugger();
						$pluginUserinfo = $userPlugin->updateUser($userinfo, 1);
						$debug = $userPlugin->debugger->get();

						if (!empty($debug[LogLevel::ERROR])) {
							$error_info[$plugin->name] = $debug[LogLevel::ERROR];
						}
						if (!empty($debug[LogLevel::DEBUG])) {
							$debug_info[$plugin->name] = $debug[LogLevel::DEBUG];
						}
						if (!$pluginUserinfo instanceof Userinfo) {
							//make sure the userinfo is available
							$pluginUserinfo = $userPlugin->getUser($userinfo);
						}
						//update the jfusion_users table
						if ($pluginUserinfo instanceof Userinfo) {
							$userPlugin->updateLookup($pluginUserinfo, $userinfo);
						}
					}
				} catch (Exception $e) {
					$error_info[$plugin->name] = array($e->getMessage());
				}
			}
		}
		$this->debugger->set('debug', $debug_info);
		$this->debugger->set('error', $error_info);
		return true;
	}

	/**
	 * Finds the first user that match starting with master
	 *
	 * @param Userinfo $userinfo
	 * @param bool     $lookup
	 *
	 * @return null|Userinfo returns first used founed
	 */
	public static function search(Userinfo $userinfo, $lookup = false)
	{
		$exsistingUser = null;

		if ($lookup && $userinfo->getJname() !== null) {
			$userPlugin = Factory::getUser($userinfo->getJname());

			$exsistingUser = $userPlugin->lookupUser($userinfo);
		}
		if (!$exsistingUser instanceof Userinfo) {
			$plugins = Factory::getPlugins();
			foreach ($plugins as $plugin) {
				try {
					$JFusionSlave = Factory::getUser($plugin->name);

					$exsistingUser = $JFusionSlave->getUser($userinfo);
					if ($exsistingUser instanceof Userinfo) {
						break;
					}
				} catch (Exception $e) {
				}
			}
		}
		return $exsistingUser;
	}

	/**
	 * Delete old user data in the lookup table
	 *
	 * @param Userinfo $userinfo userinfo of the user to be deleted
	 */
	public static function remove(Userinfo $userinfo)
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->delete('#__jfusion_users')
			->where('userid = ' . $db->quote($userinfo->userid));
		$db->setQuery($query);

		$db->execute();
	}
}
