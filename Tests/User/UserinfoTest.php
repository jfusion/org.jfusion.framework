<?php namespace JFusion\Tests\User;
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use JFusion\User\Userinfo;

/**
 * Test class for Authentication
 *
 * @since  1.0
 */
class UserinfoTest extends \PHPUnit_Framework_TestCase
{

	public function test___construct() {
		$userinfo = new Userinfo('plugin');
		$userinfo->userid = 200;
		$userinfo->username = 'test_username';
		$userinfo->email = 'user@email.com';
		$userinfo->name = 'Real Name';
		$userinfo->password = '11111';
		$userinfo->password = 'test1234567890';
		$userinfo->password_salt = 'test1234567890';
		$userinfo->password_clear = 'test1234567890';
		$userinfo->activation = 'test1234567890';


		$this->assertEquals('plugin', $userinfo->getJname());

		$this->assertTrue($userinfo->userid === 200);
		$this->assertEquals('test_username', $userinfo->username, 'username');
		$this->assertEquals('user@email.com', $userinfo->email, 'email');
		$this->assertEquals('Real Name', $userinfo->name, 'name');
		$this->assertEquals('test1234567890', $userinfo->password, 'password');
		$this->assertEquals('test1234567890', $userinfo->password_salt, 'password_salt');
		$this->assertEquals('test1234567890', $userinfo->password_clear, 'password_clear');
		$this->assertEquals('test1234567890', $userinfo->activation, 'activation');


		$userinfo->block = '1';
		$this->assertTrue($userinfo->block, 'block');
		$userinfo->block = 1;
		$this->assertTrue($userinfo->block, 'block');
		$userinfo->block = 0;
		$this->assertFalse($userinfo->block, 'block');
		$userinfo->block = '0';
		$this->assertFalse($userinfo->block, 'block');


		$foo = array('foo','baa');

		$userinfo->groups = 'invalid';
		$this->assertTrue(is_array($userinfo->groups), 'groups');
		$userinfo->groups = $foo;
		$this->assertSame($foo , $userinfo->groups, 'groups');


		$userinfo->groupnames = 'invalid';
		$this->assertTrue(is_array($userinfo->groupnames), 'groupnames');
		$userinfo->groupnames = $foo;
		$this->assertSame($foo , $userinfo->groupnames, 'groupnames');


		$userinfo->registerDate = 10000;
		$this->assertTrue($userinfo->registerDate === 10000, 'registerDate');
		$userinfo->registerDate = 'foobaa';
		$this->assertTrue($userinfo->registerDate === 0, 'registerDate');


		$userinfo->lastvisitDate = 10000;
		$this->assertTrue($userinfo->lastvisitDate === 10000, 'lastvisitDate');
		$userinfo->lastvisitDate = 'foobaa';
		$this->assertTrue($userinfo->lastvisitDate === 0, 'lastvisitDate');


		$userinfo->language = 'en-GB';
		$this->assertEquals('en-GB', $userinfo->language, 'Language');


		$anon = $userinfo->getAnonymizeed();
		$this->assertEquals('******', $anon->password_clear, 'anno: password_clear');
		$this->assertEquals('test12********', $anon->password, 'anno: password');
		$this->assertEquals('test*****', $anon->password_salt, 'anno: password_salt');
	}

	public function test_bind() {
		$userinfo = new Userinfo('plugin');

		$foo = array('foo','baa');

		$info = new \stdClass();
		$info->userid = 200;
		$info->username = 'test_username';
		$info->email = 'user@email.com';
		$info->name = 'Real Name';
		$info->password = '11111';
		$info->password = 'test1234567890';
		$info->password_salt = 'test1234567890';
		$info->password_clear = 'test1234567890';
		$info->activation = 'test1234567890';
		$info->block = 1;
		$info->groups = $foo;
		$info->groupnames = $foo;
		$info->registerDate = 10000;
		$info->lastvisitDate = 10000;
		$info->language = 'en-GB';
		$info->costume = 'costume field';

		$userinfo->bind($info);




		$this->assertEquals('plugin', $userinfo->getJname());

		$this->assertTrue($userinfo->userid === 200);
		$this->assertEquals('test_username', $userinfo->username, 'username');
		$this->assertEquals('user@email.com', $userinfo->email, 'email');
		$this->assertEquals('Real Name', $userinfo->name, 'name');
		$this->assertEquals('test1234567890', $userinfo->password, 'password');
		$this->assertEquals('test1234567890', $userinfo->password_salt, 'password_salt');
		$this->assertEquals('test1234567890', $userinfo->password_clear, 'password_clear');
		$this->assertEquals('test1234567890', $userinfo->activation, 'activation');
		$this->assertTrue($userinfo->block, 'block');
		$this->assertSame($foo , $userinfo->groups, 'groups');
		$this->assertSame($foo , $userinfo->groupnames, 'groupnames');
		$this->assertTrue($userinfo->registerDate === 10000, 'registerDate');
		$this->assertTrue($userinfo->lastvisitDate === 10000, 'lastvisitDate');
		$this->assertEquals('en-GB', $userinfo->language, 'Language');
		$this->assertEquals('costume field', $userinfo->costume, 'costume');
	}

	public function test___isset() {
		$userinfo = new Userinfo('plugin');
		$userinfo->userid = 1000;
		$this->assertTrue(isset($userinfo->userid), 'isset');
	}

	public function test_getJname() {
		$userinfo = new Userinfo('plugin');
		$this->assertEquals('plugin', $userinfo->getJname());
	}

	public function test_toObject() {
		$userinfo = new Userinfo('plugin');
		$object = $userinfo->toObject();

		$this->assertInstanceOf('\stdClass', $object, 'object');

		$this->assertEquals('plugin', $object->jname);
	}
}
