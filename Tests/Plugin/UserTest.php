<?php namespace JFusion\Tests\Plugin;
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

use JFusion\Plugin\User;
use JFusion\User\Userinfo;

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
class UserTest extends PluginTest
{
	public function test___construct() {
		$plugin = new User('none_exsisting_plugin');
		$this->assertFalse($plugin->helper);

		$plugin = new User('phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\Helper', $plugin->helper);
	}

	public function test_getUser() {
		$plugin = new User('none_exsisting_plugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$this->assertNull($plugin->getUser($userinfo));
	}

	public function test_findUser() {
		$this->markTestIncomplete();
	}

	public function test_getUserIdentifier() {
		$plugin = new User('none_exsisting_plugin');

		$userinfo = new Userinfo('none_exsisting_plugin2');
		$userinfo->userid = 2;
		$userinfo->username = 'myuser';
		$userinfo->email = 'Myuser@site.com';

		list($col, $value) = $plugin->getUserIdentifier($userinfo, 'username', 'email', 'userid');
		$this->assertSame('username', $col);
		$this->assertSame('myuser', $value);

		$plugin->params->set('login_identifier', 2);
		list($col, $value) = $plugin->getUserIdentifier($userinfo, 'username', 'email', 'userid');
		$this->assertSame('LOWER(email)', $col);
		$this->assertSame('myuser@site.com', $value);

		$plugin->params->set('login_identifier', 2);
		list($col, $value) = $plugin->getUserIdentifier($userinfo, 'username', 'email', 'userid', false);
		$this->assertSame('email', $col);
		$this->assertSame('Myuser@site.com', $value);

		$plugin->params->set('login_identifier', 3);
		list($col, $value) = $plugin->getUserIdentifier($userinfo, 'username', 'email', 'userid');
		$this->assertSame('username', $col);
		$this->assertSame('myuser', $value);

		$plugin->params->set('login_identifier', 4);
		list($col, $value) = $plugin->getUserIdentifier($userinfo, 'username', 'email', 'userid');
		$this->assertSame('userid', $col);
		$this->assertSame(2, $value);
	}

	public function test_destroySession() {
		$this->markTestSkipped();
	}

	public function test_createSession() {
		$this->markTestSkipped();
	}

	public function test_filterUsername() {
		$this->markTestSkipped();

		$plugin = new User('none_exsisting_plugin');
		$this->assertSame('username', $plugin->filterUsername('username'));
	}

	public function test_updateUser() {
		$this->markTestIncomplete();
	}

	public function test_doUpdateUsergroup() {
		$this->markTestIncomplete();
	}

	public function test_executeUpdateUsergroup() {
		$this->markTestIncomplete();
	}

	public function test_doUpdatePassword() {
		$this->markTestIncomplete();
	}

	public function test_updatePassword() {
		$this->markTestIncomplete();
	}

	public function test_updateUsername() {
		$this->markTestIncomplete();
	}

	public function test_doUpdateEmail() {
		$this->markTestIncomplete();
	}

	public function test_updateEmail() {
		$this->markTestIncomplete();
	}

	public function test_updateUsergroup() {
		$this->markTestIncomplete();
	}

	public function test_doUpdateBlock() {
		$this->markTestIncomplete();
	}

	public function test_blockUser() {
		$this->markTestIncomplete();
	}

	public function test_unblockUser() {
		$this->markTestIncomplete();
	}

	public function test_doUpdateActivate() {
		$this->markTestIncomplete();
	}

	public function test_activateUser() {
		$this->markTestIncomplete();
	}

	public function test_inactivateUser() {
		$this->markTestIncomplete();
	}

	public function test_doCreateUser() {
		$this->markTestIncomplete();
	}

	public function test_createUser() {
		$plugin = new User('none_exsisting_plugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$this->assertNull($plugin->createUser($userinfo));
	}

	public function test_deleteUser() {
		$this->markTestIncomplete();
	}

	public function test_doUserLanguage() {
		$this->markTestIncomplete();
	}

	public function test_updateUserLanguage() {
		$this->markTestIncomplete();
	}

	public function test_compareUserGroups() {
		$this->markTestIncomplete();
	}

	public function test_getUserGroupIndex() {
		$this->markTestIncomplete();
	}

	public function test_curlLogin() {
		$this->markTestSkipped();
	}

	public function test_curlReadPage() {
		$this->markTestSkipped();
	}

	public function test_curlLogout() {
		$this->markTestSkipped();
	}

	public function test_getCorrectUserGroups() {
		$this->markTestIncomplete();
	}

	public function test_addCookie() {
		$this->markTestSkipped();
	}

	public function test_lookupUser() {
		$this->markTestIncomplete();
	}

	public function test_updateLookup() {
		$this->markTestIncomplete();
	}

	public function test_deleteLookup() {
		$this->markTestIncomplete();
	}
}