<?php namespace JFusion\Tests;
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
use JFusion\Framework;
use JFusion\Tests\Abstracts\FrameworkTestCase;
use JFusion\User\Userinfo;

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

		$this->assertEquals('phpbb3', $master->name, 'should be  phpbb3');
	}

	public function test_getSlaves()
	{
		$slaves = Framework::getSlaves();

		$this->assertEquals('phpbb3_1', $slaves[0]->name, 'should be  phpbb3');

		$this->assertCount(1, $slaves, 'Expected 1 slave');
	}

	public function test_removeUser()
	{
		$userinfo = new Userinfo('foo');
		Framework::removeUser($userinfo);
	}

	public function test_parseCode()
	{
		$this->markTestIncomplete('parseCode');
	}

	public function test_getAltAvatar()
	{
		$this->markTestIncomplete('getAltAvatar');
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
			$this->assertFalse(Framework::hasFeature('none_exsisting_plugin', $feature), 'should NOT have ' . $feature);
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
			$this->assertTrue(Framework::hasFeature('phpbb3', $feature), 'should have ' . $feature);
		}
	}

	public function test_getXml() {
		$xml = Framework::getXml('<root><test>hello world!!</test></root>', false);

		$this->assertEquals('hello world!!', $xml->test, 'should: hello world!!');
	}

	public function test_raise() {
		$this->markTestSkipped('Skipping: raise');
	}

	public function test_getImageSize() {
		$this->markTestSkipped('Skipping: getImageSize');
	}

	public function test_getHash() {
		$hash = Framework::getHash('test');

		$this->assertEquals('ea2d966cf3f13addb32c1accfcaccd12', $hash, 'hash incorrect!!');
	}

	public function test_getUpdateUserGroups() {
		$this->markTestIncomplete('getUpdateUserGroups');
	}

	public function test_updateUsergroups() {
		$this->markTestIncomplete('updateUsergroups');
	}

	public function test_genRandomPassword() {
		$password = Framework::genRandomPassword();
		$this->assertTrue(strlen($password) === 8, 'Random pass not 8');
	}

	public function test_getNodeID() {
		$node = Framework::getNodeID();
		$this->assertEquals('localhost/path/to/framework', $node, 'node incorrect');
	}

	public function test_getPluginPath() {
		$root = Framework::getPluginPath();
		$this->assertEquals('/fake/path/to/plugins', $root, 'Plugin path incorrect');

		$plugin = Framework::getPluginPath('plugin');
		$this->assertEquals('/fake/path/to/plugins/plugin', $plugin, 'Plugin path incorrect');
	}

	public function test_getComposerInfo() {
		$this->markTestSkipped('Skipping: getComposerInfo');
	}
}