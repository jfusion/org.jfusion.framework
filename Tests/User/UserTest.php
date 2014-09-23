<?php namespace JFusion\Tests\User;
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

use JFusion\Tests\Abstracts\FrameworkTestCase;
use JFusion\User\User;
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
class UserTest extends FrameworkTestCase
{
	public function test_getInstance() {
		$this->markTestSkipped();
	}

	public function test_login() {
		$this->markTestSkipped();
	}

	public function test_logout() {
		$this->markTestSkipped();
	}

	public function test_delete() {
		$this->markTestSkipped();
	}

	public function test_save() {
		$this->markTestSkipped();
	}

	public function test_search() {
		$userinfo = new Userinfo('mockplugin');
		$userinfo->userid = 1;
		$userinfo->email = 'mockuser@site.com';
		$userinfo->email = 'mockuser';

		$user = User::search($userinfo, true);
		$this->assertNotNull($user);

		$user = User::search($userinfo, false);
		$this->assertNotNull($user);

		$userinfo = new Userinfo('foobaa');
		$userinfo->userid = 1;
		$userinfo->email = 'mockuser@site.com';
		$userinfo->email = 'mockuser';

		$user = User::search($userinfo, true);
		$this->assertNull($user);

		$user = User::search($userinfo, false);
		$this->assertNull($user);
	}

	public function test_remove() {
		$userinfo = new Userinfo('mockplugin');
		$userinfo->userid = 1;
		$userinfo->email = 'mockuser@site.com';
		$userinfo->email = 'mockuser';

		User::remove($userinfo);
	}
}