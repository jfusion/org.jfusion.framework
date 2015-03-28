<?php namespace JFusion\Tests;
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
use JFusion\Factory;
use JFusion\Tests\Abstracts\FrameworkTestCase;

/**
 * Test class for Authentication
 *
 * @since  1.0
 */
class FactoryTest extends FrameworkTestCase
{
	public function test_getNameFromInstance()
	{
		$name = Factory::getNameFromInstance('none_exsisting_plugin');
		$this->assertSame('none_exsisting_plugin', $name);

		$name = Factory::getNameFromInstance('mockplugin');
		$this->assertSame('mockplugin', $name);

		$name = Factory::getNameFromInstance('test');
		$this->assertSame('mockplugin', $name);
	}

	public function test_getFront()
	{
		$front = Factory::getFront('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Front', $front);

		$front = Factory::getFront('mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\Front', $front);
	}

	public function test_getAdmin()
	{
		$admin = Factory::getAdmin('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Admin', $admin);

		$admin = Factory::getAdmin('mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\Admin', $admin);
	}

	public function test_getAuth()
	{
		$auth = Factory::getAuth('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Auth', $auth);

		$auth = Factory::getAuth('mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\Auth', $auth);
	}

	public function test_getUser()
	{
		$user = Factory::getUser('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\User', $user);

		$user = Factory::getUser('mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\User', $user);
	}

	public function test_getPlatform()
	{
		$platform = Factory::getPlatform('Joomla','none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Platform\Joomla', $platform);

		$platform = Factory::getPlatform('Joomla','mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\Platform\Joomla\Platform', $platform);
	}

	public function test_getHelper()
	{
		$helper = Factory::getHelper('none_exsisting_plugin');

		$this->assertFalse($helper, 'expected to be false');

		$helper = Factory::getHelper('mockplugin');
		$this->assertInstanceOf('\JFusion\Plugins\mockplugin\Helper', $helper);
	}

	public function test_getDatabase() {
		$driver = Factory::getDatabase('mockplugin');

		$this->assertInstanceOf('\Joomla\Database\DatabaseDriver', $driver);
	}

	public function test_pluginAutoLoad() {
		$this->markTestSkipped();
	}

	public function test_getParams() {
		$params = Factory::getParams('mockplugin');

		$this->assertInstanceOf('\Joomla\Registry\Registry', $params);

		$this->assertSame('sqlite', $params->get('database_type'));
	}

	public function test_getPlugins() {
		$plugins = Factory::getPlugins();

		$this->assertSame('mockplugin', $plugins[0]->name);
		$this->assertSame('mockplugin_1', $plugins[1]->name);

		$master = Factory::getPlugins('master');
		$this->assertSame('mockplugin', $master->name);

		$slaves = Factory::getPlugins('slave');
		$this->assertSame('mockplugin_1', $slaves[1]->name);

		$plugins = Factory::getPlugins('both', 'mockplugin');
		$this->assertSame('mockplugin_1', $plugins[0]->name);
	}

	public function test_getPluginNodeId() {
		$nodeid = Factory::getPluginNodeId('mockplugin');
		$this->assertSame('localhost/path/to/mockplugin', $nodeid);
	}

	public function test_getPluginNameFromNodeId() {
		$plugin = Factory::getPluginNameFromNodeId('localhost/path/to/mockplugin');
		$this->assertSame('mockplugin', $plugin);
	}

	public function test_getCookies() {
		$cookie = Factory::getCookies();

		$this->assertInstanceOf('\JFusion\Authentication\Cookies', $cookie);
	}

	public function test_getDbo() {
		$this->markTestSkipped();
	}

	public function test_getLanguage() {
		$this->markTestIncomplete();
		/**
		 * TODO ADD ME
		 */
	}

	public function test_getDate() {
		$this->markTestIncomplete();
		/**
		 * TODO ADD ME
		 */
	}

	public function test_getDispatcher() {
		$dispatcher = Factory::getDispatcher();
		$this->assertInstanceOf('\Joomla\Event\Dispatcher', $dispatcher);
	}
}
