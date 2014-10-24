<?php namespace JFusion\Plugins\mockplugin;
/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mockplugin
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
use JFusion\Factory;

use JFusion\User\Groups;
use stdClass;

/**
 * JFusion Admin Class for phpbb3
 * For detailed descriptions on these functions please check Plugin_Admin
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mockplugin
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

class Admin extends \JFusion\Plugin\Admin
{
	/**
	 * @return string
	 */
	function getTablename()
	{
		return 'users';
	}

	/**
	 * @param string $softwarePath
	 *
	 * @return array
	 */
	function setupFromPath($softwarePath)
	{

	}

	/**
	 * Returns the a list of users of the integrated software
	 *
	 * @param int $limitstart start at
	 * @param int $limit number of results
	 *
	 * @return array
	 */
	function getUserList($limitstart = 0, $limit = 0)
	{
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->select('username, email')
			->from('#__users');

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * @return int
	 */
	function getUserCount()
	{
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->select('count(*)')
			->from('#__users');

		$db->setQuery($query);
		return (int)$db->loadResult();
	}

	/**
	 * @return array
	 */
	function getUsergroupList()
	{
		$usergrouplist = array();

		//append the default usergroup
		$default_group = new stdClass;
		$default_group->id = 1;
		$default_group->name = 'one';
		$usergrouplist[] = $default_group;

		//append the default usergroup
		$default_group = new stdClass;
		$default_group->id = 2;
		$default_group->name = 'two';
		$usergrouplist[] = $default_group;

		//append the default usergroup
		$default_group = new stdClass;
		$default_group->id = 3;
		$default_group->name = 'three';
		$usergrouplist[] = $default_group;

		return $usergrouplist;
	}

	/**
	 * @return string|array
	 */
	function getDefaultUsergroup()
	{
		$usergroup = Groups::get($this->getJname(), true);

		$group = array();
		if ($usergroup !== null) {
			$db = Factory::getDatabase($this->getJname());

			foreach($usergroup as $g) {
				if ($g != 0) {
					//we want to output the usergroup name

					$query = $db->getQuery(true)
						->select('name')
						->from('#__usergroups')
						->where('id = ' . (int)$g);

					$db->setQuery($query);
					$group[] = $db->loadResult();
				}
			}
		}
		return $group;
	}

	/**
	 * @return bool
	 */
	function allowRegistration()
	{
		return true;
	}

	/**
	 * @return array
	 */
	function uninstall()
	{
		$return = true;
		$reasons = array();

		return array($return, $reasons);
	}

	/**
	 * do plugin support multi usergroups
	 *
	 * @return bool
	 */
	function isMultiGroup()
	{
		return true;
	}

	/**
	 * do plugin support multi usergroups
	 *
	 * @return string UNKNOWN or JNO or JYES or ??
	 */
	function requireFileAccess()
	{
		return 'UNKNOWN';
	}
}
