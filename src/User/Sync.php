<?php namespace JFusion\User;
/**
 * Model that handles the usersync
 *
 * PHP version 5
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */

use JFusion\Factory;
use JFusion\Framework;

use Joomla\Language\Text;
use Joomla\Registry\Registry;

use Psr\Log\LogLevel;
use RuntimeException;

use stdClass;
use Exception;

/**
 * Class for usersync JFusion functions
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class Sync
{
	/**
	 * function will attempt to alocate more resources to run the sync
	 */
	private static function init() {
		ob_start();
		set_time_limit(0);
		ini_set('memory_limit', '256M');

		ini_set('upload_max_filesize', '128M');
		ini_set('post_max_size', '256M');
		ini_set('max_input_time', '7200');
		ini_set('max_execution_time', '0');
		ini_set('expect.timeout', '7200');
		ini_set('default_socket_timeout', '7200');
		ob_end_clean();
	}

	/**
	 * Count log data
	 *
	 * @param string $syncid the usersync id
	 * @param string $action
	 *
	 * @return Registry
	 */
	public static function initData($syncid, $action)
	{
		if ($syncid) {
			//clear sync in progress catch in case we manually stopped the sync so that the sync will continue
			self::changeStatus($syncid, 0);
		}

		$syncdata = new Registry();

		$syncdata->set('completed', false);
		$syncdata->set('sync_errors', 0);
		$syncdata->set('total_to_sync', 0);
		$syncdata->set('synced_users', 0);
		$syncdata->set('userbatch', 1);
		$syncdata->set('user_offset', 0);
		$syncdata->set('syncid', $syncid);
		$syncdata->set('action', $action);

		return $syncdata;
	}

	/**
	 * @param Registry $syncdata
	 * @param array    $slaves
	 *
	 * @return Registry
	 * @throws \RuntimeException
	 */
	public static function initiate($syncdata, $slaves)
	{
		$syncid = $syncdata->get('syncid');
		$action = $syncdata->get('action');
		if (!self::getStatus($syncid)) {
			//sync has not started, lets get going :)
			$master_plugin = Framework::getMaster();
			$master = $master_plugin->name;
			$JFusionMaster = Factory::getAdmin($master);
			if (empty($slaves)) {
				throw new RuntimeException(Text::_('SYNC_NODATA'));
			} else {
				//initialise the slave data array
				$slave_data = array();
				//lets find out which slaves need to be imported into the Master
				foreach ($slaves as $jname => $slave) {
					if ($slave == $jname) {
						$temp_data = new stdClass();
						$temp_data->jname = $jname;
						$JFusionPlugin = Factory::getAdmin($jname);
						if ($action == 'master') {
							$temp_data->total = $JFusionPlugin->getUserCount();
						} else {
							$temp_data->total = $JFusionMaster->getUserCount();
						}
						$total_to_sync = $syncdata->get('total_to_sync', 0);

						$total_to_sync += $temp_data->total;
						$syncdata->set('total_to_sync', $total_to_sync);

						//this doesn't change and used by usersync when limiting the number of users to grab at a time
						$temp_data->total_to_sync = $temp_data->total;
						$temp_data->created = 0;
						$temp_data->deleted = 0;
						$temp_data->updated = 0;
						$temp_data->error = 0;
						$temp_data->unchanged = 0;
						//save the data
						$slave_data[] = $temp_data;
						//reset the variables
						unset($temp_data, $JFusionPlugin);
					}
				}
				//format the syncdata for storage in the JFusion sync table
				$syncdata->set('master', $master);
				$syncdata->set('slave_data', $slave_data);

				//save the submitted syncdata in order for AJAX updates to work
				self::saveData($syncdata);

				//start the usersync
				self::execute($syncdata, $action);
			}
		}
		return $syncdata;
	}

	/**
	 * Retrieve log data
	 *
	 * @param string $syncid the usersync id
	 * @param string $type
	 * @param int $limitstart
	 * @param int $limit
	 * @param string $sort
	 * @param string $dir
	 *
	 * @return stdClass[] nothing
	 */
	public static function getLogData($syncid, $type = 'all', $limitstart = null, $limit = null, $sort = 'id', $dir = 'ASC')
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__jfusion_sync_details')
			->where('syncid = ' . $db->quote($syncid));

		if (!empty($sort)) {
			$query->order($sort . ' ' . $dir);
		}

		if ($type != 'all') {
			$query->where('action = ' . $db->quote($type));
		}

		$db->setQuery($query, $limitstart, $limit);
		$results = $db->loadObjectList('id');

		return $results;
	}

	/**
	 * Count log data
	 *
	 * @param string $syncid the usersync id
	 * @param string $type
	 *
	 * @return int count results
	 */
	public static function countLogData($syncid, $type = 'all')
	{
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__jfusion_sync_details')
			->where('syncid = ' . $db->quote($syncid));

		if ($type != 'all') {
			$query->where('action = ' . $db->quote($type));
		}
		$db->setQuery($query);
		return (int)$db->loadResult();
	}

	/**
	 * Save sync data
	 *
	 * @param Registry &$syncdata the actual syncdata
	 *
	 * @return string nothing
	 */
	public static function saveData(Registry $syncdata)
	{
		$db = Factory::getDBO();
		$data = new stdClass;
		$data->syncdata = $syncdata->toString();
		$data->syncid = $syncdata->get('syncid');
		$data->time_start = time();
		$data->action = $syncdata->get('action');

		$db->insertObject('#__jfusion_sync', $data);
	}

	/**
	 * Update syncdata
	 *
	 * @param Registry &$syncdata the actual syncdata
	 */
	public static function updateData(Registry $syncdata)
	{
		//find out if the syncid already exists
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->update('#__jfusion_sync')
			->set('syncdata = ' . $db->quote($syncdata->toString()))
			->where('syncid = ' . $db->quote($syncdata->get('syncid')));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Get syncdata
	 *
	 * @param string $syncid the usersync id
	 *
	 * @return Registry|null
	 */
	public static function getData($syncid)
	{
		if ($syncid) {
			$db = Factory::getDBO();

			$query = $db->getQuery(true)
				->select('syncdata')
				->from('#__jfusion_sync')
				->where('syncid = ' . $db->quote($syncid));

			$db->setQuery($query);
			$data = $db->loadResult();
			if ($data) {
				return new Registry($data);
			}
		}
		return null;
	}

	/**
	 * Fix sync errors
	 *
	 * @param string $syncid    the usersync id
	 * @param array  $syncError the actual syncError data
	 *
	 * @param        $limitstart
	 * @param        $limit
	 *
	 * @return string nothing
	 */
	public static function resolveError($syncid, $syncError, $limitstart, $limit)
	{
		$synclog = static::getLogData($syncid, 'error', $limitstart, $limit);
		foreach ($syncError as $id => $error) {
			try {
				if (isset($error['action']) && isset($synclog[$id]) && $error['action']) {
					$data = json_decode($synclog[$id]->data);

					$conflictuserinfo = $data->conflict->userinfo;
					$useruserinfo = $data->user->userinfo;

					$conflictjname = $data->conflict->jname;
					$userjname = $data->user->jname;
					if ($conflictuserinfo instanceof Userinfo && $useruserinfo instanceof Userinfo) {
						switch ($error['action']) {
							case '1':
								$userinfo = Factory::getUser($conflictjname)->getUser($conflictuserinfo);
								if ($userinfo instanceof Userinfo) {
									$userPlugin = Factory::getUser($userjname);
									$userPlugin->resetDebugger();
									if ($userPlugin->validateUser($userinfo)) {
										$userPlugin->updateUser($userinfo, 1);

										$status = $userPlugin->debugger->get();
										if (!empty($status[LogLevel::ERROR])) {
											Framework::raise(LogLevel::ERROR, $status[LogLevel::ERROR], $userjname . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE'));
										} else {
											Framework::raise(LogLevel::INFO, Text::_('USER') . ' ' . $userinfo->username . ' ' . Text::_('UPDATE'), $userjname);
											static::markResolved($id);
											$userPlugin->updateLookup($useruserinfo, $userinfo);
										}
									}
								}
								break;
							case '2':
								$userinfo = Factory::getUser($userjname)->getUser($useruserinfo);
								if ($userinfo instanceof Userinfo) {
									$userPlugin = Factory::getUser($conflictjname);
									$userPlugin->resetDebugger();
									if ($userPlugin->validateUser($userinfo)) {
										$userPlugin->updateUser($userinfo, 1);

										$status = $userPlugin->debugger->get();
										if (!empty($status[LogLevel::ERROR])) {
											Framework::raise(LogLevel::ERROR, $status[LogLevel::ERROR], $conflictjname . ' ' . Text::_('USER') . ' ' . Text::_('UPDATE'));
										} else {
											Framework::raise(LogLevel::INFO, Text::_('USER') . ' ' . $userinfo->username . ' ' . Text::_('UPDATE'), $conflictjname);
											static::markResolved($id);
											$userPlugin->updateLookup($userinfo, $useruserinfo);
										}
									}
								}
								break;
							case '3':
								//delete the first entity
								//prevent Joomla from deleting all the slaves via the user plugin if it is set as master
								global $JFusionActive;
								$JFusionActive = 1;

								$userPlugin = Factory::getUser($data->user->jname);
								$userPlugin->deleteUser($useruserinfo);

								$status = $userPlugin->debugger->get();
								if (!empty($status[LogLevel::ERROR])) {
									Framework::raise(LogLevel::ERROR, $status[LogLevel::ERROR], $data->user->jname . ' ' . Text::_('USER_DELETION_ERROR') . ': ' . $data->user->username);
								} else {
									static::markResolved($id);
									Framework::raise(LogLevel::INFO, Text::_('SUCCESS') . ' ' . Text::_('DELETING') . ' ' . Text::_('USER') . ' ' . $data->user->username, $data->user->jname);
									$userPlugin->deleteLookup($useruserinfo);
								}
								break;
							case '4':
								//delete the second entity (conflicting plugin)
								//prevent Joomla from deleting all the slaves via the user plugin if it is set as master
								global $JFusionActive;
								$JFusionActive = 1;
								$userPlugin = Factory::getUser($data->conflict->jname);
								$userPlugin->deleteUser($conflictuserinfo);

								$status = $userPlugin->debugger->get();
								if (!empty($status[LogLevel::ERROR])) {
									Framework::raise(LogLevel::ERROR, $status[LogLevel::ERROR], $data->conflict->jname . ' ' . Text::_('USER_DELETION_ERROR') . ': ' . $data->conflict->username);
								} else {
									static::markResolved($id);
									Framework::raise(LogLevel::INFO, Text::_('SUCCESS') . ' ' . Text::_('DELETING') . ' ' . Text::_('USER') . ' ' . $data->conflict->username, $data->conflict->jname);
									$userPlugin->deleteLookup($conflictuserinfo);
								}
								break;
						}
					} else {
						throw new RuntimeException('Incorrect User Object type');
					}
				}
			} catch (Exception $e) {
				Framework::raise(LogLevel::ERROR, $e);
			}
		}
	}

	/**
	 * Marks an error in sync details as resolved to prevent it from constantly showing up in the resolve error view
	 * @param $id
	 */
	public static function markResolved($id) {
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->update('#__jfusion_sync_details')
			->set('action = ' . $db->quote('resolved'))
			->where('id = ' . $db->quote($id));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Save log data
	 *
	 * @param Registry &$syncdata     the actual syncdata
	 *
	 * @return string nothing
	 */
	public static function execute(&$syncdata)
	{
		self::init();
		try {
			$action = $syncdata->get('action');
			$completed = $syncdata->get('completed');
			if (empty($completed)) {
				$master = $syncdata->get('master');
				$syncid = $syncdata->get('syncid');
				$userbatch = $syncdata->get('userbatch', 500);

				//setup some variables
				$MasterPlugin = Factory::getAdmin($master);
				$MasterUser = Factory::getUser($master);

				$db = Factory::getDBO();
				if (!static::getStatus($syncid)) {
					//tell JFusion a sync is in progress
					static::changeStatus($syncid, 1);
					$user_count = 1;
					//going to die every x users so that apache doesn't time out
					//we should start with the import of slave users into the master

					/**
					 * should be reset at end.
					 */
					$slave_data = $syncdata->get('slave_data', array());
					$plugin_offset = $syncdata->get('plugin_offset', 0);
					if (!empty($slave_data)) {
						for ($i = $plugin_offset; $i < count($slave_data); $i++) {
							$syncdata->set('plugin_offset', $i);

							$user_offset = $syncdata->get('user_offset', 0);
							//get a list of users
							$jname = $slave_data[$i]->jname;
							if ($jname) {
								$SlavePlugin = Factory::getAdmin($jname);
								$SlaveUser = Factory::getUser($jname);
								if ($action == 'master') {
									$userlist = $SlavePlugin->getUserList($user_offset, $userbatch);
									$action_name = $jname;
									$action_reverse_name = $master;
								} else {
									$userlist = $MasterPlugin->getUserList($user_offset, $userbatch);
									$action_name = $master;
									$action_reverse_name = $jname;
								}

								//catch to determine if the plugin supports limiting users for sync performance
								if (count($userlist) != $slave_data[$i]->total_to_sync) {
									//the userlist has already been limited so just start with the first one from the retrieved results
									$user_offset = 0;
								}
								//perform the actual sync
								for ($j = $user_offset; $j < count($userlist); $j++) {
									$syncdata->set('user_offset', $syncdata->get('user_offset', 0) + 1);

									$status = array();
									$userinfo = new Userinfo(null);
									$userinfo->bind($userlist[$j]);

									$UpdateUserInfo = null;
									try {
										if ($action == 'master') {
											$userinfo = $SlaveUser->getUser($userinfo);
											if ($userinfo instanceof Userinfo) {
												$MasterUser->resetDebugger();
												if ($MasterUser->validateUser($userinfo)) {
													$UpdateUserInfo = $MasterUser->updateUser($userinfo);

													$status = $MasterUser->debugger->get();

													if (!$UpdateUserInfo instanceof Userinfo) {
														//make sure the userinfo is available
														$UpdateUserInfo = $MasterUser->getUser($userinfo);
													}
												}
											}
										} else {
											$userinfo = $MasterUser->getUser($userinfo);
											if ($userinfo instanceof Userinfo) {
												$SlaveUser->resetDebugger();
												if ($MasterUser->validateUser($userinfo)) {
													$UpdateUserInfo = $SlaveUser->updateUser($userinfo);

													$status = $SlaveUser->debugger->get();

													if (!$UpdateUserInfo instanceof Userinfo) {
														//make sure the userinfo is available
														$UpdateUserInfo = $SlaveUser->getUser($userinfo);
													}
												}
											}
										}
									} catch (Exception $e) {
										$status[LogLevel::ERROR] = $e->getMessage();
										$UpdateUserInfo = null;
									}

									$sync_log = new stdClass;
									$sync_log->syncid = $syncid;
									$sync_log->jname = $jname;
									$sync_log->message = '';
									$sync_log->data = '';

									$sync_log->username = $userlist[$j]->username;
									$sync_log->email = $userlist[$j]->email;

									if (!$userinfo instanceof Userinfo || !empty($status[LogLevel::ERROR])) {
										$status['action'] = 'error';
										$sync_log->message = (is_array($status[LogLevel::ERROR])) ? implode('; ', $status[LogLevel::ERROR]) : $status[LogLevel::ERROR];
										$error = new stdClass();
										$error->conflict = new stdClass();
										$error->conflict->userinfo = $UpdateUserInfo;
										$error->conflict->error = $status[LogLevel::ERROR];
										$error->conflict->debug = (!empty($status[LogLevel::DEBUG])) ? $status[LogLevel::DEBUG] : '';
										$error->conflict->jname = $action_reverse_name;
										$error->user = new stdClass();
										$error->user->jname = $action_name;
										$error->user->userinfo = $userinfo;
										$error->user->userlist = $userlist[$j];

										$sync_log->type = 'ERROR';
										if (!empty($error->conflict->userinfo->username) && ($error->user->userinfo->username != $error->conflict->userinfo->username)) {
											$sync_log->type = 'USERNAME';
										} else if (!empty($error->conflict->userinfo->email) && $error->user->userinfo->email != $error->conflict->userinfo->email) {
											$sync_log->type = 'EMAIL';
										}

										$sync_log->data = json_encode($error);
										$syncdata->set('sync_errors', $syncdata->get('sync_errors', 0) + 1);
									} else {
										//usersync loggin enabled
										$sync_log->username = isset($UpdateUserInfo->username) ? $UpdateUserInfo->username : $userinfo->username;
										$sync_log->email = isset($UpdateUserInfo->email) ? $UpdateUserInfo->email : $userinfo->email;
										if ($UpdateUserInfo instanceof Userinfo) {
											//update the lookup table
											if ($action == 'master') {
												$MasterUser->updateLookup($UpdateUserInfo, $userinfo);
											} else {
												$SlaveUser->updateLookup($userinfo, $UpdateUserInfo);
											}
										}
									}
									$sync_log->action = $status['action'];

									//append the error to the log
									$db->insertObject('#__jfusion_sync_details', $sync_log);

									//update the counters
									$slave_data[$i]->{$status['action']} += 1;
									$slave_data[$i]->total -= 1;

									$syncdata->set('slave_data', $slave_data);

									$syncdata->set('synced_users', $syncdata->get('synced_users', 0) + 1);
									//update the database, only store syncdata every 20 users for better performance
									if ($user_count >= 20) {
										if ($slave_data[$i]->total == 0) {
											//will force the next plugin and first user of that plugin on resume
											$syncdata->set('plugin_offset', $syncdata->get('plugin_offset', 0) + 1);
											$syncdata->set('user_offset', 0);
										}
										static::updateData($syncdata);
										//update counters
										$user_count = 1;
									} else {
										//update counters
										$user_count++;
									}
									$userbatch--;

									if ($syncdata->get('synced_users', 0) == $syncdata->get('total_to_sync', 0)) {
										break;
									} elseif ($userbatch == 0 || $slave_data[$i]->total == 0) {
										//exit the process to prevent an apache timeout; it will resume on the next ajax call
										//save the syncdata before exiting
										if ($slave_data[$i]->total == 0) {
											//will force  the next plugin and first user of that plugin on resume
											$syncdata->set('plugin_offset', $syncdata->get('plugin_offset', 0) + 1);
											$syncdata->set('user_offset', 0);
										}
										static::updateData($syncdata);
										//tell Joomla the batch has completed
										static::changeStatus($syncid, 0);
										return;
									}
								}
							}
						}
					}

					$syncdata->set('slave_data', $slave_data);
					if ($syncdata->get('synced_users', 0) == $syncdata->get('total_to_sync', 0)) {
						//end of sync, save the final data

						$syncdata->set('completed', true);
						static::updateData($syncdata);

						//update the finish time
						$db = Factory::getDBO();

						$query = $db->getQuery(true)
							->update('#__jfusion_sync')
							->set('time_end = ' . $db->quote(time()))
							->where('syncid = ' . $db->quote($syncid));

						$db->setQuery($query);
						$db->execute();
					}
					static::updateData($syncdata);
					static::changeStatus($syncid, 0);
				}
			}
		} catch (Exception $e) {
			Framework::raise(LogLevel::ERROR, $e);
		}
	}

	/**
	 * @static
	 * @param $syncid
	 * @param $status
	 */
	public static function changeStatus($syncid, $status) {
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->update('#__jfusion_sync')
			->set('active = ' . (int) $status)
			->where('syncid = ' . $db->quote($syncid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @static
	 * @param string $syncid
	 *
	 * @return int|mixed
	 */
	public static function getStatus($syncid) {
		if (!empty($syncid)) {
			$db = Factory::getDBO();

			$query = $db->getQuery(true)
				->select('active')
				->from('#__jfusion_sync')
				->where('syncid = ' . $db->quote($syncid));

			$db->setQuery($query);
			return (int)$db->loadResult();
		}
		return 0;
	}

	/**
	 * @static
	 * @param string $syncid
	 *
	 * @return boolean
	 */
	public static function exsists($syncid) {
		if (!empty($syncid)) {
			$db = Factory::getDBO();

			$query = $db->getQuery(true)
				->select('syncid')
				->from('#__jfusion_sync')
				->where('syncid = ' . $db->quote($syncid));

			$db->setQuery($query);

			if ($db->loadResult()) {
				return true;
			}
		}
		return false;
	}
}