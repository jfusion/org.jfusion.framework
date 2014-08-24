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
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertCount(0, $plugin->getUserList());

		$plugin = Factory::getAdmin('mockplugin');
		$this->assertCount(2, $plugin->getUserList());
	}

	public function test_getUserCount() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertSame(0, $plugin->getUserCount());

		$plugin = Factory::getAdmin('mockplugin');
		$this->assertSame(2, $plugin->getUserCount());
	}

	public function test_getUsergroupList() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertCount(0, $plugin->getUsergroupList());

		$plugin = Factory::getAdmin('mockplugin');
		$this->assertCount(3, $plugin->getUsergroupList());
	}

	public function test_getDefaultUsergroup() {
		$plugin = Factory::getAdmin('mockplugin');

		$groups = $plugin->getDefaultUsergroup();
		$this->assertCount(1, $groups);

		$this->assertSame('one', $groups[0]);
	}

	public function test_allowRegistration() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertTrue($plugin->allowRegistration());

		$plugin = Factory::getAdmin('mockplugin');
		$this->assertTrue($plugin->allowRegistration());
	}

	public function test_getTablename() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertSame('', $plugin->getTablename());
	}

	public function test_setupFromPath() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertCount(0, $plugin->setupFromPath(''));
	}

	public function test_checkConfig() {
		$plugin = Factory::getAdmin('mockplugin');
		$this->assertTrue($plugin->checkConfig());
	}

	public function test_updateStatus() {
		$plugin = Factory::getAdmin('mockplugin');

		$db = Factory::getDBO();

		$query = $db->getQuery(true)
			->select('status')
			->from('#__jfusion')
			->where('name = ' . $db->quote('mockplugin'));
		$db->setQuery($query);

		$this->assertSame('1', $db->loadResult());

		$plugin->updateStatus(0);

		$query = $db->getQuery(true)
			->select('status')
			->from('#__jfusion')
			->where('name = ' . $db->quote('mockplugin'));
		$db->setQuery($query);

		$this->assertSame('0', $db->loadResult());
	}

	public function test_debugConfig() {
		$this->markTestSkipped();
	}

	public function test_allowEmptyCookiePath() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertFalse($plugin->allowEmptyCookiePath());
	}

	public function test_allowEmptyCookieDomain() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertFalse($plugin->allowEmptyCookieDomain());
	}

	public function test_debugConfigExtra() {
		$this->markTestSkipped();
	}

	public function test_uninstall() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertCount(2, $plugin->uninstall());
	}

	public function test_isMultiGroup() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertFalse($plugin->isMultiGroup());
	}

	public function test_requireFileAccess() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertSame('UNKNOWN', $plugin->requireFileAccess());
	}

	public function test_multiInstance() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');
		$this->assertTrue($plugin->multiInstance());
	}

	public function test_readFile() {
		$this->markTestIncomplete();
	}

	public function test_getRenderGroupe() {
		$plugin = Factory::getAdmin('none_exsisting_plugin');

		$js = <<<JS
		JFusion.renderPlugin['none_exsisting_plugin'] = JFusion.renderDefault;
JS;

		$this->assertSame($js, $plugin->getRenderGroup());
	}

	public function test_saveParameters() {
		$this->markTestIncomplete();
	}
}