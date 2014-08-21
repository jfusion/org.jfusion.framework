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

use JFusion\Plugin\Front;
use JFusion\Plugin\Platform;

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
class FrontTest extends PluginTest
{
	public function test___construct() {
		$this->markTestSkipped();
	}

	public function test_getLostPasswordURL() {
		$plugin = new Front('none_exsisting_plugin');
		$this->assertSame('', $plugin->getLostPasswordURL());
	}

	public function test_getLostUsernameURL() {
		$plugin = new Front('none_exsisting_plugin');
		$this->assertSame('', $plugin->getLostUsernameURL());
	}

	public function test_getRegistrationURL() {
		$plugin = new Front('none_exsisting_plugin');
		$this->assertSame('', $plugin->getRegistrationURL());
	}
}