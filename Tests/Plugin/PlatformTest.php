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
class PlatformTest extends PluginTest
{
	public function test___construct() {
		$this->markTestSkipped();
	}

	public function test_hasFile() {
		$plugin = new Platform('none_exsisting_plugin');
		$this->assertFalse($plugin->hasFile('foo.php'));

		$file = $plugin->hasFile('Platform.php');

		$this->assertStringEndsWith('Platform.php', $file);
	}

	public function test_uninstall() {
		$plugin = new Platform('none_exsisting_plugin');
		$this->assertCount(2, $plugin->uninstall());
	}

	public function test_parseRoute() {
		$this->markTestSkipped();
	}

	public function test_buildRoute() {
		$this->markTestSkipped();
	}
}