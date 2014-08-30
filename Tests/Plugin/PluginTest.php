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

use JFusion\Plugin\Plugin;
use JFusion\Tests\Abstracts\FrameworkTestCase;

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
class PluginTest extends FrameworkTestCase
{
	public function test___construct() {
		$plugin = new Plugin('none_exsisting_plugin');

		$this->assertSame('Plugin', $plugin->getName());
		$this->assertSame('none_exsisting_plugin', $plugin->getJname());

		$this->markTestIncomplete();
	}

	public function test_methodDefined() {
		$plugin = new Plugin('none_exsisting_plugin');

		$this->assertFalse($plugin->methodDefined('getPluginFile'));
	}

	public function test_isConfigured() {
		$plugin = new Plugin('none_exsisting_plugin');

		$this->assertFalse($plugin->isConfigured());

		$plugin = new Plugin('mockplugin');

		$this->assertTrue($plugin->isConfigured());
	}

	public function test_getPluginFile() {
		$this->markTestSkipped();
	}

	public function test_resetDebugger() {
		$this->markTestSkipped();
	}

	public function test_genRandomPassword() {
		$plugin = new Plugin('none_exsisting_plugin');

		$password = $plugin->genRandomPassword();
		$this->assertSame(8, strlen($password));
	}
}