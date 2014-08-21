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

use JFusion\Plugin\Admin;
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
class AdminTest extends PluginTest
{
	public function test___construct() {
		$this->markTestSkipped();
	}

	public function test_getUserList() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertCount(0, $plugin->getUserList());
	}

	public function test_getUserCount() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertSame(0, $plugin->getUserCount());
	}

	public function test_getUsergroupList() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertCount(0, $plugin->getUsergroupList());
	}

	public function test_getDefaultUsergroup() {
		$this->markTestIncomplete();
	}

	public function test_allowRegistration() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertTrue($plugin->allowRegistration());
	}

	public function test_getTablename() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertSame('', $plugin->getTablename());
	}

	public function test_setupFromPath() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertCount(0, $plugin->setupFromPath(''));
	}

	public function test_checkConfig() {
		$this->markTestSkipped();
	}

	public function test_updateStatus() {
		$this->markTestIncomplete();
	}

	public function test_debugConfig() {
		$this->markTestSkipped();
	}

	public function test_allowEmptyCookiePath() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertFalse($plugin->allowEmptyCookiePath());
	}

	public function test_allowEmptyCookieDomain() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertFalse($plugin->allowEmptyCookieDomain());
	}

	public function test_debugConfigExtra() {
		$this->markTestSkipped();
	}

	public function test_uninstall() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertCount(2, $plugin->uninstall());
	}

	public function test_isMultiGroup() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertFalse($plugin->isMultiGroup());
	}

	public function test_requireFileAccess() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertSame('UNKNOWN', $plugin->requireFileAccess());
	}

	public function test_multiInstance() {
		$plugin = new Admin('none_exsisting_plugin');
		$this->assertTrue($plugin->multiInstance());
	}

	public function test_readFile() {
		$this->markTestIncomplete();
	}

	public function test_getRenderGroupe() {
		$plugin = new Admin('none_exsisting_plugin');

		$js = <<<JS
		JFusion.renderPlugin['none_exsisting_plugin'] = JFusion.renderDefault;
JS;

		$this->assertSame($js, $plugin->getRenderGroup());
	}

	public function test_saveParameters() {
		$this->markTestIncomplete();
	}
}