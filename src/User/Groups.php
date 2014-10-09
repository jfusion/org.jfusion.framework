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

use Joomla\Registry\Registry;
use Psr\Log\LogLevel;

use stdClass;

/**
 * Joomla! CMS Application class
 *
 * @package     Joomla.Libraries
 * @subpackage  Application
 * @since       3.2
 */
class Groups
{
	/**
	 * @var    User  The application instance.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 *
	 */
	function __construct()
	{
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
			static::$instance = new Groups();
		}
		return static::$instance;
	}

	/**
	 * @param string $jname
	 * @param bool   $default
	 *
	 * @return mixed;
	 */
	public static function get($jname = '', $default = false) {
		static $usergroups;
		if (!isset($usergroups)) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('value')
				->from('#__jfusion_settings')
				->where($db->quoteName('key') . ' = ' . $db->quote('user.groups'));

			$db->setQuery($query);

			$usergroups = $db->loadResult();

			if ($usergroups) {
				$usergroups = new Registry($usergroups);

				$usergroups = $usergroups->toObject();
			} else {
				$usergroups = false;
			}
		}

		if ($jname) {
			if (isset($usergroups->{$jname})) {
				$groups = $usergroups->{$jname};
				if ($default) {
					if (isset($groups[0])) {
						$groups = $groups[0];
					} else {
						$groups = null;
					}
				}
			} else {
				if ($default) {
					$groups = null;
				} else {
					$groups = array();
				}
			}
		} else {
			$groups = $usergroups;
		}

		return $groups;
	}

	/**
	 * @return stdClass
	 */
	public static function getUpdate() {
		static $updateusergroups;
		if (!isset($updateusergroups)) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('value')
				->from('#__jfusion_settings')
				->where($db->quoteName('key') . ' = ' . $db->quote('user.groups.update'));

			$db->setQuery($query);

			$updateusergroups = $db->loadResult();

			if ($updateusergroups) {
				$updateusergroups = new Registry($updateusergroups);

				$updateusergroups = $updateusergroups->toObject();
			} else {
				$updateusergroups = new stdClass();
			}
		}

		return $updateusergroups;
	}

	/**
	 * @param $groups
	 */
	public static function save($groups) {
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->delete('#__jfusion_settings')
			->where($db->quoteName('key') . ' = ' . $db->quote('user.groups'));

		$db->setQuery($query);
		$db->execute();

		$groups = new Registry($groups);

		$insert = new stdClass();
		$insert->key =  'user.groups';
		$insert->value =  $groups->toString();
		$db->insertObject('#__jfusion_settings', $insert);
	}

	/**
	 * @param $update
	 */
	public static function saveUpdate($update) {
		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->delete('#__jfusion_settings')
			->where($db->quoteName('key') . ' = ' . $db->quote('user.groups.update'));

		$db->setQuery($query);
		$db->execute();

		$update = new Registry($update);

		$insert = new stdClass();
		$insert->key =  'user.groups.update';
		$insert->value =  $update->toString();
		$db->insertObject('#__jfusion_settings', $insert);
	}


	/**
	 * returns true / false if the plugin is in advanced usergroup mode or not...
	 *
	 * @param string $jname plugin name
	 *
	 * @return boolean
	 */
	public static function isUpdate($jname) {
		$updateusergroups = static::getUpdate();
		$advanced = false;
		if (isset($updateusergroups->{$jname}) && $updateusergroups->{$jname}) {
			$master = Framework::getMaster();
			if ($master->name != $jname) {
				$advanced = true;
			}
		}
		return $advanced;
	}
}
