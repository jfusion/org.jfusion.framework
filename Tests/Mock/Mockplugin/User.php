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
use JFusion\User\Userinfo;

use Joomla\Language\Text;

use \RuntimeException;
use \stdClass;

/**
 * JFusion User Class for phpBB3
 * For detailed descriptions on these functions please check Plugin_User
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mockplugin
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class User extends \JFusion\Plugin\User
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

	/**
	 * @param Userinfo $userinfo
	 *
	 * @return null|Userinfo
	 */
	function getUser(Userinfo $userinfo) {
		$user = null;

		//get the identifier
		list($identifier_type, $identifier) = $this->getUserIdentifier($userinfo, 'username', 'email', 'userid');
		// Get a database object
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->select('*')
			->from('#__users')
			->where($db->quoteName($identifier_type) . ' = ' . $db->quote($identifier));

		$db->setQuery($query);

		$result = $db->loadObject();
		if ($result) {
			$user = new Userinfo($this->getJname());

			$query = $db->getQuery(true)
				->select('*')
				->from('#__groups')
				->where('userid = ' . $db->quote($result->userid));
			$db->setQuery($query);
			$groups = $db->loadObjectList();

			$g = array();
			foreach($groups as $group) {
				$g[] = (int)$group->group;
			}

			$result->groups = $g;

			$user->bind($result);
		}
		return $user;
	}

	/**
	 * @param Userinfo $userinfo
	 * @param array $options
	 *
	 * @return array
	 */
	function destroySession(Userinfo $userinfo, $options)
	{
		/**
		 * TODO: add mock
		 */
	}

	/**
	 * @param Userinfo $userinfo
	 * @param array $options
	 * @return array
	 */
	function createSession(Userinfo $userinfo, $options) {
		/**
		 * TODO: add mock
		 */
	}

	/**
	 * used to validate if a user can be created or not
	 * should throw exception if user can't be created with info about the error.
	 *
	 * @param $userinfo
	 *
	 * @return boolean
	 */
	function validateUser(Userinfo $userinfo)
	{
		if (strpos($userinfo->username, '%') !== false) {
			throw new RuntimeException('Useranme Has Invalid Character: %');
		}
		return true;
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function updatePassword(Userinfo $userinfo, Userinfo &$existinguser) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('password = ' . $db->quote(md5($userinfo->password_clear)))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function updateUsername(Userinfo $userinfo, Userinfo &$existinguser) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('username = ' . $db->quote($userinfo->username))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function updateEmail(Userinfo $userinfo, Userinfo &$existinguser) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('email = ' . $db->quote($userinfo->email))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @throws RuntimeException
	 * @return void
	 */
	public function updateUsergroup(Userinfo $userinfo, Userinfo &$existinguser)
	{
		$usergroups = $this->getCorrectUserGroups($userinfo);

		if (empty($usergroups)) {
			throw new RuntimeException(Text::_('ADVANCED_GROUPMODE_MASTERGROUP_NOTEXIST'));
		} else {
			$db = Factory::getDatabase($this->getJname());

			$query = $db->getQuery(true)
				->delete('#__groups')
				->where('userid = ' . $db->quote($existinguser->userid));
			$db->setQuery($query);
			$db->execute();

			foreach($usergroups as $group) {
				$g = new stdClass;
				$g->userid = $existinguser->userid;
				$g->group = $group;
				$db->insertObject('#__groups', $g);
			}
		}
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function blockUser(Userinfo $userinfo, Userinfo &$existinguser) {
		//block the user
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('block = ' . $db->quote($userinfo->block))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function unblockUser(Userinfo $userinfo, Userinfo &$existinguser) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('block = ' . $db->quote($userinfo->block))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function activateUser(Userinfo $userinfo, Userinfo &$existinguser) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('activation = ' . $db->quote($userinfo->activation))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 * @param Userinfo $existinguser
	 *
	 * @return void
	 */
	function inactivateUser(Userinfo $userinfo, Userinfo &$existinguser) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('activation = ' . $db->quote($userinfo->activation))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * @param Userinfo $userinfo
	 *
	 * @throws \RuntimeException
	 *
	 * @return Userinfo
	 */
	function createUser(Userinfo $userinfo) {
		$db = Factory::getDatabase($this->getJname());

		$usergroups = $this->getCorrectUserGroups($userinfo);
		if (empty($usergroups)) {
			throw new RuntimeException(Text::_('USERGROUP_MISSING'));
		} else {
			//prepare the variables
			$user = new stdClass;
			$user->userid = null;
			$user->username = $userinfo->username;
			$user->email = $userinfo->email;
			$user->password = $userinfo->password;

			$db->insertObject('#__users', $user, 'userid');

			foreach($usergroups as $group) {
				$g = new stdClass;
				$g->userid = $user->userid;
				$g->group = $group;

				$db->insertObject('#__groups', $g);
			}

			//return the good news
			return $this->getUser($userinfo);
		}
	}

	/**
	 * @param Userinfo $userinfo
	 *
	 * @throws \RuntimeException
	 *
	 * @return boolean returns true on success and false on error
	 */
	function deleteUser(Userinfo $userinfo) {
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->delete('#__users')
			->where('userid = ' . $db->quote($userinfo->userid));

		$db->setQuery($query);
		$db->execute();
		return true;
	}

	/**
	 * Function that update the language of a user
	 *
	 * @param Userinfo $userinfo Object containing the existing userinfo
	 * @param Userinfo $existinguser         Object JLanguage containing the current language of Joomla
	 */
	function updateUserLanguage(Userinfo $userinfo, Userinfo &$existinguser)
	{
		$db = Factory::getDatabase($this->getJname());

		$query = $db->getQuery(true)
			->update('#__users')
			->set('language = ' . $db->quote($userinfo->language))
			->where('userid = ' . $db->quote($existinguser->userid));

		$db->setQuery($query);
		$db->execute();
	}
}