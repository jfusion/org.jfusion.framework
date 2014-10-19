<?php namespace JFusion\User;
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use JFusion\Application\Application;
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
	protected $debugger;

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
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function login($credentials, $options = array())
	{
		if (!isset($credentials['email'])) {
			$credentials['email'] = null;
		}
		if (!isset($credentials['fullname'])) {
			$credentials['fullname'] = null;
		}
		if (!isset($credentials['userinfo'])) {
			$credentials['userinfo'] = null;
		}

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

			if (!empty($JFusionActivePlugin)) {
				$options['skipplugin'][] = $JFusionActivePlugin;
			}

			//allow for the detection of external mods to exclude jfusion plugins
			$jnodeid = Application::getInstance()->input->get('jnodeid', null);
			if ($jnodeid) {
				$jnodeid = strtolower($jnodeid);
				$JFusionActivePlugin = $jnodeid;
				$options['skipplugin'][] = $jnodeid;
			}

			//determine if overwrites are allowed
			if ($options['overwrite']) {
				$overwrite = 1;
			} else {
				$overwrite = 0;
			}

			//get the JFusion master
			$master = Framework::getMaster();
			if (!$master) {
				throw new RuntimeException(Text::_('NO_MASTER'));
			} else {
				$MasterUserPlugin = Factory::getUser($master->name);
				//check to see if userinfo is already present

				if ($credentials['userinfo'] instanceof Userinfo) {
					//the jfusion auth plugin is enabled
					$this->debugger->add('init', Text::_('USING_JFUSION_AUTH'));

					$userinfo = $credentials['userinfo'];
				} else {
					$this->debugger->add('init', Text::_('USING_OTHER_AUTH'));
					//other auth plugin enabled get the userinfo again
					//temp userinfo to see if the user exists in the master

					$authUserinfo = new Userinfo('joomla_int');
					$authUserinfo->username = $credentials['username'];
					$authUserinfo->email = $credentials['email'];
					$authUserinfo->password_clear = $credentials['password'];
					$authUserinfo->name = $credentials['fullname'];

					//get the userinfo for real
					try {
						$userinfo = $MasterUserPlugin->getUser($authUserinfo);
					} catch (Exception $e) {
						$userinfo = null;
					}

					if (!$userinfo instanceof Userinfo) {
						//should be auto-create users?
						$params = Factory::getParams($master->name);
						$autoregister = $params->get('autoregister', 0);
						if ($autoregister == 1) {
							try {
								$this->debugger->add('init', Text::_('CREATING_MASTER_USER'));
								//try to create a Master user

								$MasterUserPlugin->resetDebugger();
								if ($MasterUserPlugin->validateUser($authUserinfo)) {
									$userinfo = $MasterUserPlugin->doCreateUser($authUserinfo);
									$this->debugger->add('init', Text::_('MASTER') . ' ' . Text::_('USER') . ' ' . Text::_('CREATE') . ' ' . Text::_('SUCCESS'));
								}
							} catch (Exception $e) {
								throw new RuntimeException($master->name . ' ' . Text::_('USER') . ' ' . Text::_('CREATE') . ' ' . Text::_('ERROR') . ' ' . $e->getMessage());
							}
						} else {
							//return an error
							$this->debugger->add('init', Text::_('COULD_NOT_FIND_USER'));
							throw new RuntimeException(Text::_('COULD_NOT_FIND_USER'));
						}
					}
				}

				if ($userinfo instanceof Userinfo) {
					if ($success === 0) {
						//apply the clear text password to the user object
						$userinfo->password_clear = $credentials['password'];

						if ($userinfo->block || $userinfo->activation) {
							//make sure the block is also applied in slave software
							$slaves = Framework::getSlaves();
							foreach ($slaves as $slave) {
								try {
									if (!in_array($slave->name, $options['skipplugin'])) {
										$JFusionSlave = Factory::getUser($slave->name);
										$JFusionSlave->resetDebugger();
										if ($JFusionSlave->validateUser($userinfo)) {
											$SlaveUserInfo = $JFusionSlave->updateUser($userinfo, $overwrite);

											$SlaveUser = $JFusionSlave->debugger->get();
											if (!$SlaveUserInfo instanceof UserInfo) {
												//make sure the userinfo is available
												$SlaveUserInfo = $JFusionSlave->getUser($userinfo);
											}
											if (!empty($SlaveUser[LogLevel::ERROR])) {
												$this->debugger->set($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR'), $SlaveUser[LogLevel::ERROR]);
											}
											if (!empty($SlaveUser[LogLevel::DEBUG])) {
												$this->debugger->set($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('DEBUG'), $SlaveUser[LogLevel::DEBUG]);
											}

											$this->debugger->set($slave->name . ' ' . Text::_('USERINFO'), $SlaveUserInfo);
										}
									}
								} catch (Exception $e) {
									Framework::raise(LogLevel::ERROR, $e, $slave->name);
									$this->debugger->add($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR'), $e->getMessage());
								}
							}
							if ($userinfo->block) {
								throw new RuntimeException(Text::_('FUSION_BLOCKED_USER'));
							} else {
								throw new RuntimeException(Text::_('FUSION_INACTIVE_USER'));
							}
						} else {
							if (!in_array($master->name, $options['skipplugin']) && $master->dual_login == 1) {
								if ($userinfo->canLogin()) {
									try {
										$MasterSession = $MasterUserPlugin->createSession($userinfo, $options);

										if (!empty($MasterSession[LogLevel::ERROR])) {
											$this->debugger->set($master->name . ' ' . Text::_('SESSION') . ' ' . Text::_('ERROR'), $MasterSession[LogLevel::ERROR]);
											Framework::raise(LogLevel::ERROR, $MasterSession[LogLevel::ERROR], $master->name . ': ' . Text::_('SESSION') . ' ' . Text::_('CREATE'));
											/**
											 * TODO replace below code ? or just login slaves as well?
											 */
											if ($master->name == 'joomla_int') {
												$success = -1;
											}
										}
										if (!empty($MasterSession[LogLevel::DEBUG])) {
											$this->debugger->set($master->name . ' ' . Text::_('SESSION') . ' ' . Text::_('DEBUG'), $MasterSession[LogLevel::DEBUG]);
											//report the error back
										}
									} catch (Exception $e) {
										$this->debugger->set($master->name . ' ' . Text::_('SESSION') . ' ' . Text::_('ERROR'), $e->getMessage());
										Framework::raise(LogLevel::ERROR, $e, $master->name . ': ' . Text::_('SESSION') . ' ' . Text::_('CREATE'));
										/**
										 * TODO replace below code ? or just login slaves as well?
										 */
										if ($master->name == 'joomla_int') {
											$success = -1;
										}
									}
								} else {
									$this->debugger->addDebug($master->name . ' ' . Text::_('SESSION') . ' ' . Text::_('DEBUG') . ': ' . Text::_('FUSION_BLOCKED_USER'));
								}
							}
							if ($success === 0) {
								//allow for joomlaid retrieval in the loginchecker

								$MasterUserPlugin->updateLookup($userinfo, $userinfo);

								//setup the other slave JFusion plugins
								$slaves = Factory::getPlugins('slave');
								foreach ($slaves as $slave) {
									try {
										$SlaveUserPlugin = Factory::getUser($slave->name);
										$SlaveUserPlugin->resetDebugger();
										if ($SlaveUserPlugin->validateUser($userinfo)) {
											$SlaveUserInfo = $SlaveUserPlugin->updateUser($userinfo, $overwrite);


											$SlaveUser = $SlaveUserPlugin->debugger->get();
											if (!empty($SlaveUser[LogLevel::DEBUG])) {
												$this->debugger->set($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('DEBUG'), $SlaveUser[LogLevel::DEBUG]);
											}
											if (!empty($SlaveUser[LogLevel::ERROR])) {
												$this->debugger->set($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR'), $SlaveUser[LogLevel::ERROR]);
												Framework::raise(LogLevel::ERROR, $SlaveUser[LogLevel::ERROR], $slave->name . ': ' . Text::_('USER') . ' ' . Text::_('UPDATE'));
											} else {
												if (!$SlaveUserInfo instanceof UserInfo) {
													//make sure the userinfo is available
													$SlaveUserInfo = $SlaveUserPlugin->getUser($userinfo);
												}

												if ($SlaveUserInfo instanceof UserInfo) {
													if (isset($options['show_unsensored'])) {
														$details = $SlaveUserInfo->toObject();
													} else {
														$details = $SlaveUserInfo->getAnonymizeed();
													}

													$this->debugger->set($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE'), $details);

													//apply the clear text password to the user object
													$SlaveUserInfo->password_clear = $credentials['password'];

													$SlaveUserPlugin->updateLookup($SlaveUserInfo, $userinfo);

													if (!in_array($slave->name, $options['skipplugin']) && $slave->dual_login == 1) {
														if ($userinfo->canLogin()) {
															try {
																$SlaveSession = $SlaveUserPlugin->createSession($SlaveUserInfo, $options);
																if (!empty($SlaveSession[LogLevel::ERROR])) {
																	$this->debugger->set($slave->name . ' ' . Text::_('SESSION') . ' ' . Text::_('ERROR'), $SlaveSession[LogLevel::ERROR]);
																	Framework::raise(LogLevel::ERROR, $SlaveSession[LogLevel::ERROR], $slave->name . ': ' . Text::_('SESSION') . ' ' . Text::_('CREATE'));
																}
																if (!empty($SlaveSession[LogLevel::DEBUG])) {
																	$this->debugger->set($slave->name . ' ' . Text::_('SESSION') . ' ' . Text::_('DEBUG'), $SlaveSession[LogLevel::DEBUG]);
																}
															} catch (Exception $e) {
																$this->debugger->set($slave->name . ' ' . Text::_('SESSION') . ' ' . Text::_('ERROR'), $e->getMessage());
																Framework::raise(LogLevel::ERROR, $e, $SlaveUserPlugin->getJname());
															}
														} else {
															$this->debugger->addDebug($slave->name . ' ' . Text::_('SESSION') . ' ' . Text::_('DEBUG') . ': ' . Text::_('FUSION_BLOCKED_USER'));
														}
													}
												}
											}
										}
									} catch (Exception $e) {
										Framework::raise(LogLevel::ERROR, $e, $slave->name);
										$this->debugger->addError($e->getMessage());
									}
								}
								$success = 1;
							}
						}
					}
				}
			}
		} catch (Exception $e) {
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
	public function logout(Userinfo $userinfo = null, $options = array())
	{
		//initialise some vars
		global $JFusionActive;
		$JFusionActive = true;

		if (!isset($options['skipplugin'])) {
			$options['skipplugin'] = array();
		}

		if (!empty($JFusionActivePlugin)) {
			$options['skipplugin'][] = $JFusionActivePlugin;
		}

		//allow for the detection of external mods to exclude jfusion plugins
		global $JFusionActivePlugin;

		$jnodeid = strtolower(Application::getInstance()->input->get('jnodeid'));
		if (!empty($jnodeid)) {
			$JFusionActivePlugin = $jnodeid;
			$options['skipplugin'][] = $jnodeid;
		}

		//prevent any output by the plugins (this could prevent cookies from being passed to the header)
		//logout from the JFusion plugins if done through frontend

		//get the JFusion master
		$master = Framework::getMaster();
		if ($master) {
			if (!in_array($master->name, $options['skipplugin'])) {
				$JFusionMaster = Factory::getUser($master->name);
				$userlookup = $JFusionMaster->lookupUser($userinfo);
				$this->debugger->set('userlookup', $userlookup);
				if ($userlookup instanceof Userinfo) {
					$details = null;
					try {
						$MasterUser = $JFusionMaster->getUser($userlookup);
					} catch (Exception $e) {
						$MasterUser = null;
					}
					if ($MasterUser instanceof Userinfo) {
						if (isset($options['show_unsensored'])) {
							$details = $MasterUser->toObject();
						} else {
							$details = $MasterUser->getAnonymizeed();
						}

						try {
							$MasterSession = $JFusionMaster->destroySession($MasterUser, $options);
							if (!empty($MasterSession[LogLevel::ERROR])) {
								Framework::raise(LogLevel::ERROR, $MasterSession[LogLevel::ERROR], $master->name . ': ' . Text::_('SESSION') . ' ' . Text::_('DESTROY'));
							}
							if (!empty($MasterSession[LogLevel::DEBUG])) {
								$this->debugger->set($master->name . ' logout', $MasterSession[LogLevel::DEBUG]);
							}
						} catch (Exception $e) {
							Framework::raise(LogLevel::ERROR, $e, $JFusionMaster->getJname());
						}
					} else {
						Framework::raise(LogLevel::NOTICE, Text::_('LOGOUT') . ' ' . Text::_('COULD_NOT_FIND_USER'), $master->name);
					}
					$this->debugger->set('masteruser', $details);
				}
			}

			$slaves = Factory::getPlugins('slave');
			foreach ($slaves as $slave) {
				if (!in_array($slave->name, $options['skipplugin'])) {
					//check if sessions are enabled
					if ($slave->dual_login == 1) {
						$JFusionSlave = Factory::getUser($slave->name);
						$userlookup = $JFusionSlave->lookupUser($userinfo);
						if ($userlookup instanceof Userinfo) {
							$info = null;
							try {
								$SlaveUser = $JFusionSlave->getUser($userlookup);
							} catch (Exception $e) {
								$SlaveUser = null;
							}
							if ($SlaveUser instanceof Userinfo) {
								if (isset($options['show_unsensored'])) {
									$info = $SlaveUser->toObject();
								} else {
									$info = $SlaveUser->getAnonymizeed();
								}

								$SlaveSession = array();
								try {
									$SlaveSession = $JFusionSlave->destroySession($SlaveUser, $options);
									if (!empty($SlaveSession[LogLevel::ERROR])) {
										Framework::raise(LogLevel::ERROR, $SlaveSession[LogLevel::ERROR], $slave->name . ': ' . Text::_('SESSION') . ' ' . Text::_('DESTROY'));
									}
									if (!empty($SlaveSession[LogLevel::DEBUG])) {
										$this->debugger->set($slave->name . ' logout', $SlaveSession[LogLevel::DEBUG]);
									}
								} catch (Exception $e) {
									Framework::raise(LogLevel::ERROR, $e, $JFusionSlave->getJname());
								}
							} else {
								Framework::raise(LogLevel::NOTICE, Text::_('LOGOUT') . ' ' . Text::_('COULD_NOT_FIND_USER'), $slave->name);
							}

							$this->debugger->set($slave->name . ' ' . Text::_('USER') . ' ' . Text::_('DETAILS') , $info);
						}
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
		//delete the master user if it is not Joomla
		$master = Framework::getMaster();
		if ($master) {
			$params = Factory::getParams($master->name);
			if ($params->get('allow_delete_users', 0)) {
				$JFusionMaster = Factory::getUser($master->name);
				try {
					$MasterUser = $JFusionMaster->getUser($userinfo);

					if ($MasterUser instanceof Userinfo) {
						$JFusionMaster->resetDebugger();
						$deleteStatus = $JFusionMaster->deleteUser($MasterUser);
						$status = $JFusionMaster->debugger->get();
						if ($deleteStatus) {
							$status[LogLevel::DEBUG][] = Text::_('USER_DELETION') . ': ' . $userinfo->userid . ' ( ' . $userinfo->username . ' )';
						}
						if (!empty($status[LogLevel::ERROR])) {
							$error_info[$master->name . ' ' . Text::_('USER_DELETION_ERROR') ] = $status[LogLevel::ERROR];
						}
						if (!empty($status[LogLevel::DEBUG])) {
							$debug_info[$master->name] = $status[LogLevel::DEBUG];
						}
					} else {
						$debug_info[$master->name] = Text::_('NO_USER_DATA_FOUND');
					}
				} catch (Exception $e) {
					$error_info[$master->name . ' ' . Text::_('USER_DELETION_ERROR') ] = $e->getMessage();
				}
			} else {
				$debug_info[$master->name] = Text::_('DELETE_DISABLED');
			}

			//delete the user in the slave plugins
			$slaves = Factory::getPlugins('slave');
			foreach ($slaves as $slave) {
				$params = Factory::getParams($slave->name);
				if ($params->get('allow_delete_users', 0)) {
					$JFusionSlave = Factory::getUser($slave->name);
					try {
						$SlaveUser = $JFusionSlave->getUser($userinfo);

						if ($SlaveUser instanceof Userinfo) {
							$JFusionSlave->resetDebugger();
							$deleteStatus = $JFusionSlave->deleteUser($SlaveUser);
							$status = $JFusionSlave->debugger->get();
							if ($deleteStatus) {
								$status[LogLevel::DEBUG][] = Text::_('USER_DELETION') . ': ' . $userinfo->userid . ' ( ' . $userinfo->username . ' )';
							}
							if (!empty($status[LogLevel::ERROR])) {
								$error_info[$slave->name . ' ' . Text::_('USER_DELETION_ERROR') ] = $status[LogLevel::ERROR];
							}
							if (!empty($status[LogLevel::DEBUG])) {
								$debug_info[$slave->name] = $status[LogLevel::DEBUG];
							}
						} else {
							$debug_info[$slave->name] = Text::_('NO_USER_DATA_FOUND');
						}
					} catch (Exception $e) {
						$error_info[$slave->name . ' ' . Text::_('USER_DELETION_ERROR') ] = $e->getMessage();
					}
				} else {
					$debug_info[$slave->name] = Text::_('DELETE') . ' ' . Text::_('DISABLED');
				}
			}
			//remove userlookup data
			User::remove($userinfo);
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

		//check to see if we need to update the master
		$master = Framework::getMaster();
		if ($master) {
			// Recover the old data of the user
			// This is then used to determine if the username was changed
			$updateUsername = false;
			$master_userinfo = null;
			$exsistingUser = null;

			if ($userinfo->getJname() !== null && $olduserinfo->getJname() !== null) {
				if ($new == false && $userinfo instanceof Userinfo && $olduserinfo instanceof Userinfo) {
					if ($userinfo->getJname() == $olduserinfo->getJname() && $userinfo->username != $olduserinfo->username) {
						$updateUsername = true;
					}
				}
			}
			$exsistingUser = User::search($olduserinfo, true);

			try {
				$JFusionMaster = Factory::getUser($master->name);

				if ($exsistingUser instanceof Userinfo) {
					if ($updateUsername) {
						$master_userinfo = $JFusionMaster->getUser($exsistingUser);

						if ($master_userinfo instanceof Userinfo) {
							try {
								$JFusionMaster->resetDebugger();
								$JFusionMaster->updateUsername($userinfo, $master_userinfo);
								if (!$JFusionMaster->debugger->isEmpty('error')) {
									$error_info[$master->name . ' ' . Text::_('USERNAME') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR') ] = $JFusionMaster->debugger->get('error');
								}
								if (!$JFusionMaster->debugger->isEmpty('debug')) {
									$debug_info[$master->name . ' ' . Text::_('USERNAME') . ' ' . Text::_('UPDATE') . ' ' . Text::_('DEBUG') ] = $JFusionMaster->debugger->get('debug');
								}
							} catch (Exception $e) {
								$status[LogLevel::ERROR][] = Text::_('USERNAME_UPDATE_ERROR') . ': ' . $e->getMessage();
							}
						} else {
							$error_info[$master->name] = Text::_('NO_USER_DATA_FOUND');
						}
					}
				}
				//run the update user to ensure any other userinfo is updated as well
				$JFusionMaster->resetDebugger();
				if ($JFusionMaster->validateUser($userinfo)) {
					$master_userinfo = $JFusionMaster->updateUser($userinfo, 1);
					$MasterUser = $JFusionMaster->debugger->get();

					if (!empty($MasterUser[LogLevel::ERROR])) {
						$error_info[$master->name] = $MasterUser[LogLevel::ERROR];
					}
					if (!empty($MasterUser[LogLevel::DEBUG])) {
						$debug_info[$master->name] = $MasterUser[LogLevel::DEBUG];
					}
					if (!$master_userinfo instanceof Userinfo) {
						//make sure the userinfo is available
						$master_userinfo = $JFusionMaster->getUser($userinfo);
					}
					//update the jfusion_users table
					if ($master_userinfo instanceof Userinfo) {
						$JFusionMaster->updateLookup($master_userinfo, $userinfo);
					}
				}

			} catch (Exception $e) {
				$error_info[$master->name] = array($e->getMessage());
			}

			if ($master_userinfo instanceof Userinfo) {
				if ( !empty($userinfo->password_clear) ) {
					$master_userinfo->password_clear = $userinfo->password_clear;
				}
				//update the user details in any JFusion slaves
				$slaves = Factory::getPlugins('slave');
				foreach ($slaves as $slave) {
					try {
						$JFusionSlave = Factory::getUser($slave->name);
						//if the username was updated, call the updateUsername function before calling updateUser
						if ($exsistingUser instanceof Userinfo) {
							if ($updateUsername) {
								$slave_userinfo = $JFusionSlave->getUser($exsistingUser);
								if ($slave_userinfo instanceof Userinfo) {
									try {
										$JFusionSlave->resetDebugger();
										$JFusionSlave->updateUsername($master_userinfo, $slave_userinfo);
										if (!$JFusionSlave->debugger->isEmpty('error')) {
											$error_info[$slave->name . ' ' . Text::_('USERNAME') . ' ' . Text::_('UPDATE') . ' ' . Text::_('ERROR') ] = $JFusionSlave->debugger->get('error');
										}
										if (!$JFusionSlave->debugger->isEmpty('debug')) {
											$debug_info[$slave->name . ' ' . Text::_('USERNAME') . ' ' . Text::_('UPDATE') . ' ' . Text::_('DEBUG') ] = $JFusionSlave->debugger->get('debug');
										}
									}  catch (Exception $e) {
										$status[LogLevel::ERROR][] = Text::_('USERNAME_UPDATE_ERROR') . ': ' . $e->getMessage();
									}
								} else {
									$error_info[$slave->name] = Text::_('NO_USER_DATA_FOUND');
								}
							}
						}
						$JFusionSlave->resetDebugger();
						if ($JFusionSlave->validateUser($userinfo)) {
							$SlaveUserInfo = $JFusionSlave->updateUser($master_userinfo, 1);

							$SlaveUser = $JFusionSlave->debugger->get();

							if (!empty($SlaveUser[LogLevel::ERROR])) {
								$error_info[$slave->name] = $SlaveUser[LogLevel::ERROR];
							}
							if (!empty($SlaveUser[LogLevel::DEBUG])) {
								$debug_info[$slave->name] = $SlaveUser[LogLevel::DEBUG];
							}

							if (!$SlaveUserInfo instanceof Userinfo) {
								//make sure the userinfo is available
								$SlaveUserInfo = $JFusionSlave->getUser($userinfo);
							}
							//update the jfusion_users table
							if ($SlaveUserInfo instanceof Userinfo) {
								$JFusionSlave->updateLookup($SlaveUserInfo, $userinfo);
							}
						}
					} catch (Exception $e) {
						$error_info[$slave->name] = $debug_info[$slave->name] + array($e->getMessage());
					}
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
			$master = Framework::getMaster();
			if ($master) {
				try {
					$JFusionMaster = Factory::getUser($master->name);
					$exsistingUser = $JFusionMaster->getUser($userinfo);
				} catch (Exception $e) {
				}
			}
			if (!$exsistingUser instanceof Userinfo) {
				$slaves = Factory::getPlugins('slave');
				foreach ($slaves as $slave) {
					try {
						$JFusionSlave = Factory::getUser($slave->name);
						//if the username was updated, call the updateUsername function before calling updateUser
						$exsistingUser = $JFusionSlave->getUser($userinfo);
						if ($exsistingUser instanceof Userinfo) {
							break;
						}
					} catch (Exception $e) {
					}
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
		/**
		 * TODO: need to be change to remove the user correctly with the new layout.
		 */
		//Delete old user data in the lookup table
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->delete('#__jfusion_users')
			->where('userid = ' . $db->quote($userinfo->userid));
		$db->setQuery($query);

		$db->execute();
	}
}
