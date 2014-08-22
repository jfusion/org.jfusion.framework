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
use JFusion\Plugin\Auth;
use JFusion\Plugin\Platform;
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
class AuthTest extends PluginTest
{
	public function test___construct() {
		$this->markTestSkipped();
	}

	public function test_generateEncryptedPassword() {
		$plugin = Factory::getAuth('none_exsisting_plugin');

		$userinfo = new Userinfo('none_exsisting_plugin');
		$this->assertSame('', $plugin->generateEncryptedPassword($userinfo));
	}

	public function test_checkPassword() {
		$plugin = Factory::getAuth('none_exsisting_plugin');

		$userinfo = new Userinfo('none_exsisting_plugin');

		$userinfo->password = '';
		$this->assertTrue($plugin->checkPassword($userinfo));
		$userinfo->password = 'aa';
		$this->assertFalse($plugin->checkPassword($userinfo));
	}

	public function test_comparePassword() {
		$plugin = Factory::getAuth('none_exsisting_plugin');
		$this->assertTrue($plugin->comparePassword('foo','foo'));
		$this->assertFalse($plugin->comparePassword('foo','baa'));
	}
}