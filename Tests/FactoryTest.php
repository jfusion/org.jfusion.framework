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
		$this->assertEquals('none_exsisting_plugin', $name , 'result should be: none_exsisting_plugin');

		$name = Factory::getNameFromInstance('phpbb3');
		$this->assertEquals('phpbb3', $name, 'result should be: phpbb3');

		$name = Factory::getNameFromInstance('test');
		$this->assertEquals('phpbb3', $name, 'result should be: phpbb3');
	}

	public function test_getFront()
	{
		$front = Factory::getFront('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Front', $front, '\JFusion\Plugin\Front Is expected');

		$front = Factory::getFront('phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\Front', $front, 'Not instanceof \JFusion\Plugins\phpbb3\Front');
	}

	public function test_getAdmin()
	{
		$admin = Factory::getAdmin('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Admin', $admin, '\JFusion\Plugin\Admin Is expected');

		$admin = Factory::getAdmin('phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\Admin', $admin, 'Not instanceof \JFusion\Plugins\phpbb3\Admin');
	}

	public function test_getAuth()
	{
		$auth = Factory::getAuth('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Auth', $auth, '\JFusion\Plugin\Auth Is expected');

		$auth = Factory::getAuth('phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\Auth', $auth, 'Not instanceof \JFusion\Plugins\phpbb3\Auth');
	}

	public function test_getUser()
	{
		$user = Factory::getUser('none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\User', $user, '\JFusion\Plugin\Auth Is expected');

		$user = Factory::getUser('phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\User', $user, 'Not instanceof \JFusion\Plugins\phpbb3\User');
	}

	public function test_getPlatform()
	{
		$platform = Factory::getPlatform('Joomla','none_exsisting_plugin');

		$this->assertInstanceOf('\JFusion\Plugin\Platform\Joomla', $platform, '\JFusion\Plugin\Platform\Joomla Is expected');

		$platform = Factory::getPlatform('Joomla','phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\Platform\Joomla\Platform', $platform, 'Not instanceof \JFusion\Plugins\phpbb3\Platform\Joomla\Platform');
	}

	public function test_getHelper()
	{
		$helper = Factory::getHelper('none_exsisting_plugin');

		$this->assertFalse($helper, 'expected to be false');

		$helper = Factory::getHelper('phpbb3');
		$this->assertInstanceOf('\JFusion\Plugins\phpbb3\Helper', $helper, 'Not instanceof \JFusion\Plugins\phpbb3\Helper');
	}

	public function test_getDatabase() {
		$driver = Factory::getDatabase('phpbb3');

		$this->assertInstanceOf('\Joomla\Database\DatabaseDriver', $driver, 'Not instanceof \Joomla\Database\DatabaseDriver');
	}

	public function test_pluginAutoLoad() {
		$this->markTestSkipped('Skipped pluginAutoLoad');
	}

	public function test_getParams() {
		$params = Factory::getParams('phpbb3');

		$this->assertInstanceOf('\Joomla\Registry\Registry', $params, 'Not instanceof \Joomla\Registry\Registry');

		$this->assertEquals('sqlite', $params->get('database_type'), 'Not sqlite');
	}

	public function test_createParams() {
		$this->markTestSkipped('Skipped createParams');
	}

	public function test_createDatabase() {
		$this->markTestSkipped('Skipped createDatabase');
	}

	public function test_getPlugins() {
		$plugins = Factory::getPlugins();

		$this->assertEquals('phpbb3', $plugins[0]->name, 'Expected: phpbb3');
		$this->assertEquals('phpbb3_1', $plugins[1]->name, 'Expected: phpbb3_1');

		$plugins = Factory::getPlugins('master');
		$this->assertEquals('phpbb3', $plugins[0]->name, 'Expected: phpbb3');

		$plugins = Factory::getPlugins('slave');
		$this->assertEquals('phpbb3_1', $plugins[0]->name, 'Expected: phpbb3');

		$plugins = Factory::getPlugins('both', 'phpbb3');
		$this->assertEquals('phpbb3_1', $plugins[0]->name, 'Expected: phpbb3_1');
	}

	public function test_getPluginNodeId() {
		$nodeid = Factory::getPluginNodeId('phpbb3');
		$this->assertEquals('localhost/path/to/phpbb3', $nodeid, 'Expected: localhost/path/to/phpbb3');
	}

	public function test_getPluginNameFromNodeId() {
		$plugin = Factory::getPluginNameFromNodeId('localhost/path/to/phpbb3');
		$this->assertEquals('phpbb3', $plugin, 'Expected: phpbb3');
	}

	public function test_getCookies() {
		$cookie = Factory::getCookies();

		$this->assertInstanceOf('\JFusion\Cookies\Cookies', $cookie, 'Not instanceof \JFusion\Cookies\Cookies');
	}

	public function test_getDbo() {
		$this->markTestSkipped('Skipped createParams');
	}

	public function test_getConfig() {
		$config = Factory::getConfig();

		$this->assertEquals('sqlite', $config->get('dbtype'), 'Expected: sqlite');
	}

	public function test_getLanguage() {
		$this->markTestIncomplete('getLanguage');
		/**
		 * TODO ADD ME
		 */
	}

	public function test_getDate() {
		$this->markTestIncomplete('getDate');
		/**
		 * TODO ADD ME
		 */
	}

	public function test_getDispatcher() {
		$dispatcher = Factory::getDispatcher();
		$this->assertInstanceOf('\Joomla\Event\Dispatcher', $dispatcher, 'Not instanceof \Joomla\Event\Dispatcher');
	}
}
