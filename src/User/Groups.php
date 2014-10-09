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
	static $groups;

	static $update;

	/**
	 * @param string $jname
	 * @param bool   $default
	 *
	 * @return mixed;
	 */
	public static function get($jname = '', $default = false) {
		if (!isset($groups)) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('value')
				->from('#__jfusion_settings')
				->where($db->quoteName('key') . ' = ' . $db->quote('user.groups'));

			$db->setQuery($query);

			$groups = $db->loadResult();

			if ($groups) {
				$groups = new Registry($groups);

				$groups = $groups->toObject();
			} else {
				$groups = false;
			}
		}

		if ($jname) {
			if (isset($groups->{$jname})) {
				$usergroups = $groups->{$jname};
				if ($default) {
					if (isset($usergroups[0])) {
						$usergroups = $usergroups[0];
					} else {
						$usergroups = null;
					}
				}
			} else {
				if ($default) {
					$usergroups = null;
				} else {
					$usergroups = array();
				}
			}
		} else {
			$usergroups = $groups;
		}

		return $usergroups;
	}

	/**
	 * @return stdClass
	 */
	public static function getUpdate() {
		if (!isset($update)) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('value')
				->from('#__jfusion_settings')
				->where($db->quoteName('key') . ' = ' . $db->quote('user.groups.update'));

			$db->setQuery($query);

			$update = $db->loadResult();

			if ($update) {
				$update = new Registry($update);

				$update = $update->toObject();
			} else {
				$update = new stdClass();
			}
		}
		return $update;
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
