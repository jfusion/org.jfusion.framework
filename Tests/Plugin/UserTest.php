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

use JFusion\Factory;
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
		$plugin = Factory::getUser('none_exsisting_plugin');
		$this->assertFalse($plugin->helper);

		$plugin = Factory::getUser('mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\Helper', $plugin->helper);
	}

	public function test_getUser() {
		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';

		$plugin = Factory::getUser('none_exsisting_plugin');
		$this->assertNull($plugin->getUser($userinfo));


		$plugin = Factory::getUser('mockplugin');
		$user = $plugin->getUser($userinfo);
		$this->assertInstanceOf('\JFusion\User\Userinfo', $user);
		$this->assertSame($userinfo->username, $user->username);
	}

	public function test_findUser() {
		$userinfo = new Userinfo('mockplugin');
		$userinfo->username = 'mockuser';

		$plugin = Factory::getUser('mockplugin_1');
		$founed = $plugin->findUser($userinfo);

		$this->assertSame('3', $founed->userid);
	}

	public function test_getUserIdentifier() {
		$plugin = Factory::getUser('none_exsisting_plugin');

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

	public function validateUser() {
		$this->markTestIncomplete();

		$plugin = Factory::getUser('none_exsisting_plugin');
		$this->assertSame('username', $plugin->validateUser('username'));

		$plugin = Factory::getUser('mockplugin');
		$this->assertSame('username_lol', $plugin->validateUser('username%lol'));
	}

	public function test_updateUser() {
		$this->markTestIncomplete();
	}

	public function test_updatePassword() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';
		$userinfo->password_clear = 'mockuser2';

		$exsisting = $plugin->getUser($userinfo);
		$plugin->updatePassword($userinfo, $exsisting);

		$exsisting = $plugin->getUser($userinfo);
		$this->assertSame(md5($userinfo->password_clear), $exsisting->password);
	}

	public function test_updateUsername() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';

		$exsisting = $plugin->getUser($userinfo);

		$userinfo->username = 'mockuser10';

		$plugin->updateUsername($userinfo, $exsisting);

		$exsisting = $plugin->getUser($userinfo);
		$this->assertSame($userinfo->username, $exsisting->username);
	}

	public function test_updateEmail() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';
		$userinfo->email = 'mockuser5@site.com';

		$exsisting = $plugin->getUser($userinfo);
		$plugin->updateEmail($userinfo, $exsisting);

		$exsisting = $plugin->getUser($userinfo);
		$this->assertSame($userinfo->email, $exsisting->email);
	}

	public function test_updateUsergroup() {
		$master = Factory::getUser('mockplugin');
		$slave = Factory::getUser('mockplugin_1');

		$lookup = new Userinfo('mockplugin');
		$lookup->username = 'mockuser';

		$userinfo = $master->getUser($lookup);
		$exsisting = $slave->getUser($lookup);

		$this->assertCount(1, $exsisting->groups);
		$slave->updateUsergroup($userinfo, $exsisting);

		$exsisting = $slave->getUser($lookup);

		$this->assertCount(2, $exsisting->groups);
		$this->assertSame(2, $exsisting->groups[0]);
		$this->assertSame(5, $exsisting->groups[1]);
	}

	public function test_blockUser() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';
		$userinfo->block = 1;

		$exsisting = $plugin->getUser($userinfo);
		$plugin->blockUser($userinfo, $exsisting);
	}

	public function test_unblockUser() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';
		$userinfo->block = 0;

		$exsisting = $plugin->getUser($userinfo);
		$plugin->unblockUser($userinfo, $exsisting);
	}

	public function test_activateUser() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';

		$exsisting = $plugin->getUser($userinfo);
		$plugin->activateUser($userinfo, $exsisting);

		$exsisting = $plugin->getUser($userinfo);
		$this->assertNull($exsisting->activation);
	}

	public function test_inactivateUser() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';
		$userinfo->activation = 'aaaa';

		$exsisting = $plugin->getUser($userinfo);
		$plugin->inactivateUser($userinfo, $exsisting);

		$exsisting = $plugin->getUser($userinfo);
		$this->assertSame('aaaa', $exsisting->activation);
	}

	public function test_createUser() {
		$plugin = Factory::getUser('none_exsisting_plugin');

		$newuser = new Userinfo('none_exsisting_plugin');
		$newuser->username = 'newuser';
		$newuser->email = 'newuser@site.com';
		$newuser->password = '0354d89c28ec399c00d3cb2d094cf093';
		$newuser->password_clear = 'newuser';
		$newuser->groups = array(1);

		$this->assertNull($plugin->createUser($newuser));

		$plugin = Factory::getUser('mockplugin');
		$userinfo = $plugin->createUser($newuser);

		$this->assertEquals(3, $userinfo->userid);
		$this->assertSame($newuser->username, $userinfo->username);
		$this->assertSame($newuser->email, $userinfo->email);
		$this->assertSame($newuser->password, $userinfo->password);
	}

	public function test_deleteUser() {
		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->userid = 1;

		$plugin = Factory::getUser('mockplugin');
		$this->assertTrue($plugin->deleteUser($userinfo));
	}

	public function test_updateUserLanguage() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->username = 'mockuser';
		$userinfo->language = 'da-DK';

		$exsisting = $plugin->getUser($userinfo);

		$plugin->updateUserLanguage($userinfo, $exsisting);

		$exsisting = $plugin->getUser($userinfo);
		$this->assertSame($userinfo->language, $exsisting->language);
	}

	public function test_compareUserGroups() {
		$plugin = Factory::getUser('mockplugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$userinfo->groups = array(1);

		$this->assertTrue($plugin->compareUserGroups($userinfo , array(1)));
		$this->assertFalse($plugin->compareUserGroups($userinfo , array(1, 2)));

		$userinfo->groups = array(1, 2);

		$this->assertFalse($plugin->compareUserGroups($userinfo , array(1)));
		$this->assertTrue($plugin->compareUserGroups($userinfo , array(1, 2)));
	}

	public function test_getUserGroupIndex() {
		$newuser = new Userinfo('none_exsisting_plugin');
		$newuser->groups = array(1);

		$plugin = Factory::getUser('mockplugin');
		$this->assertSame(0, $plugin->getUserGroupIndex($newuser));

		$newuser->groups = array(1, 2);
		$plugin = Factory::getUser('mockplugin');
		$this->assertSame(1, $plugin->getUserGroupIndex($newuser));

		$newuser->groups = array(3);
		$plugin = Factory::getUser('mockplugin');
		$this->assertSame(2, $plugin->getUserGroupIndex($newuser));
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
		$plugin = Factory::getUser('mockplugin_1');

		$newuser = new Userinfo('asdasd');

		$newuser->groups = array(1, 2);
		$exsisting = $plugin->getCorrectUserGroups($newuser);
		$this->assertCount(2, $exsisting);
		$this->assertSame(2, $exsisting[0]);
		$this->assertSame(5, $exsisting[1]);

		$newuser->groups = array(1);
		$exsisting = $plugin->getCorrectUserGroups($newuser);
		$this->assertCount(1, $exsisting);
		$this->assertSame(1, $exsisting[0]);

		$newuser->groups = array(3);
		$exsisting = $plugin->getCorrectUserGroups($newuser);
		$this->assertCount(1, $exsisting);
		$this->assertSame(3, $exsisting[0]);

		$newuser->groups = array(7);
		$exsisting = $plugin->getCorrectUserGroups($newuser);
		$this->assertCount(1, $exsisting);
		$this->assertSame(1, $exsisting[0]);
	}

	public function test_addCookie() {
		$this->markTestSkipped();
	}

	public function test_lookupUser() {
		$plugin = Factory::getUser('mockplugin_1');

		$newuser = new Userinfo('mockplugin');
		$newuser->username = 'mockuser';
		$newuser->userid = 1;
		$newuser->email = 'mockuser3@site.com';

		$lookedup = $plugin->lookupUser($newuser);

		$this->assertSame('3', $lookedup->userid);
		$this->assertSame('mockuser3', $lookedup->username);
		$this->assertSame('mockuser3@site.com', $lookedup->email);
	}

	public function test_updateLookup() {
		$plugin = Factory::getUser('mockplugin_1');

		$update = new Userinfo('mockplugin');
		$update->username = 'mockuser20';
		$update->userid = 20;
		$update->email = 'mockuser20@site.com';

		$update2 = new Userinfo('mockplugin_1');
		$update2->username = 'mockuser20';
		$update2->userid = 20;
		$update2->email = 'mockuser20@site.com';
		$lookedup = $plugin->lookupUser($update2);

		$this->assertTrue($plugin->updateLookup($update2, $update));
		$lookedup = $plugin->lookupUser($update2);
		$this->assertSame('20', $lookedup->userid);

		$update2->email = 'mockuser25@site.com';
		$this->assertTrue($plugin->updateLookup($update2, $update));
		$lookedup = $plugin->lookupUser($update2);
		$this->assertSame('mockuser25@site.com', $lookedup->email);
	}

	public function test_deleteLookup() {
		$plugin = Factory::getUser('mockplugin_1');

		$lookup = new Userinfo('mockplugin');
		$lookup->username = 'mockuser';
		$lookup->userid = 1;
		$lookup->email = 'mockuser3@site.com';

		$lookedup = $plugin->lookupUser($lookup);

		$this->assertTrue($plugin->deleteLookup($lookedup));
		$this->assertNull($plugin->lookupUser($lookup));
	}
}