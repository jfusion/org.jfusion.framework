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

	}

	/**
	 * @return int
	 */
	function getUserCount()
	{

	}

	/**
	 * @return array
	 */
	function getUsergroupList()
	{

	}

	/**
	 * @return string|array
	 */
	function getDefaultUsergroup()
	{

	}

	/**
	 * @return bool
	 */
	function allowRegistration()
	{

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

	}

	/**
	 * do plugin support multi usergroups
	 *
	 * @return string UNKNOWN or JNO or JYES or ??
	 */
	function requireFileAccess()
	{

	}
}
