<?php namespace JFusion\Tests;
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
use JFusion\Framework;
use JFusion\Tests\Abstracts\FrameworkTestCase;

/**
 * Test class for Authentication
 *
 * @since  1.0
 */
class FrameworkTest extends FrameworkTestCase
{
	public function test_getMaster()
	{
		$master = Framework::getMaster();

		$this->assertSame('mockplugin', $master->name);
	}

	public function test_getSlaves()
	{
		$slaves = Framework::getSlaves();

		$this->assertSame('mockplugin_1', $slaves[0]->name);

		$this->assertCount(1, $slaves);
	}

	public function test_parseCode()
	{
		$this->markTestIncomplete();
	}

	public function test_getAltAvatar()
	{
		$this->markTestIncomplete();
	}

	public function test_hasFeature()
	{
		$features = array('wizard',
			'useractivity',
			'duallogin',
			'duallogout',
			'updatepassword',
			'updateusername',
			'updateemail',
			'updateusername',
			'updateusergroup',
			'updateuserlanguage',
			'blockuser',
			'activateuser',
			'deleteuser');

		foreach($features as $feature) {
			$this->assertFalse(Framework::hasFeature('none_exsisting_plugin', $feature));
		}

		$features = array('wizard',
			'useractivity',
			'duallogin',
			'duallogout',
			'updatepassword',
			'updateusername',
			'updateemail',
			'updateusername',
			'updateusergroup',
			'blockuser',
			'activateuser',
			'deleteuser');

		foreach($features as $feature) {
			$this->assertTrue(Framework::hasFeature('mockplugin', $feature));
		}
	}

	public function test_getXml() {
		$xml = Framework::getXml('<root><test>hello world!!</test></root>', false);

		$this->assertSame('hello world!!', (string)$xml->test);
	}

	public function test_raise() {
		$this->markTestSkipped();
	}

	public function test_getImageSize() {
		$this->markTestSkipped();
	}

	public function test_getHash() {
		$hash = Framework::getHash('test');

		$this->assertSame('ea2d966cf3f13addb32c1accfcaccd12', $hash);
	}

	public function test_getUpdateUserGroups() {
		$this->markTestIncomplete();
	}

	public function test_updateUsergroups() {
		$this->markTestIncomplete();
	}

	public function test_getNodeID() {
		$node = Framework::getNodeID();
		$this->assertSame('localhost/path/to/framework', $node);
	}

	public function test_getPluginPath() {
		$root = Framework::getPluginPath();
		$this->assertSame('/fake/path/to/plugins', $root);

		$plugin = Framework::getPluginPath('plugin');
		$this->assertSame('/fake/path/to/plugins/plugin', $plugin);
	}

	public function test_getComposerInfo() {
		$this->markTestSkipped();
	}
}