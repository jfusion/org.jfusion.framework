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


		$this->assertSame('plugin', $userinfo->getJname());
		$this->assertSame(200, $userinfo->userid);
		$this->assertSame('test_username', $userinfo->username);
		$this->assertSame('user@email.com', $userinfo->email);
		$this->assertSame('Real Name', $userinfo->name);
		$this->assertSame('test1234567890', $userinfo->password);
		$this->assertSame('test1234567890', $userinfo->password_salt);
		$this->assertSame('test1234567890', $userinfo->password_clear);
		$this->assertSame('test1234567890', $userinfo->activation);


		$userinfo->block = '1';
		$this->assertTrue($userinfo->block);
		$userinfo->block = 1;
		$this->assertTrue($userinfo->block);
		$userinfo->block = 0;
		$this->assertFalse($userinfo->block);
		$userinfo->block = '0';
		$this->assertFalse($userinfo->block);


		$foo = array('foo','baa');

		$userinfo->groups = 'invalid';
		$this->assertTrue(is_array($userinfo->groups));
		$userinfo->groups = $foo;
		$this->assertSame($foo , $userinfo->groups);


		$userinfo->groupnames = 'invalid';
		$this->assertTrue(is_array($userinfo->groupnames));
		$userinfo->groupnames = $foo;
		$this->assertSame($foo , $userinfo->groupnames);


		$userinfo->registerDate = 10000;
		$this->assertSame(10000, $userinfo->registerDate);
		$userinfo->registerDate = 'foobaa';
		$this->assertSame(0, $userinfo->registerDate);


		$userinfo->lastvisitDate = 10000;
		$this->assertSame(10000, $userinfo->lastvisitDate);
		$userinfo->lastvisitDate = 'foobaa';
		$this->assertSame(0, $userinfo->lastvisitDate);


		$userinfo->language = 'en-GB';
		$this->assertSame('en-GB', $userinfo->language);


		$anonymizeed = $userinfo->getAnonymizeed();
		$this->assertSame('******', $anonymizeed->password_clear);
		$this->assertSame('test12********', $anonymizeed->password);
		$this->assertSame('test*****', $anonymizeed->password_salt);
	}

	public function test_bind() {
		$userinfo = new Userinfo('plugin');

		$foo = array('foo', 'baa');

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

		$this->assertSame('plugin', $userinfo->getJname());
		$this->assertSame(200, $userinfo->userid);
		$this->assertSame('test_username', $userinfo->username);
		$this->assertSame('user@email.com', $userinfo->email);
		$this->assertSame('Real Name', $userinfo->name);
		$this->assertSame('test1234567890', $userinfo->password);
		$this->assertSame('test1234567890', $userinfo->password_salt);
		$this->assertSame('test1234567890', $userinfo->password_clear);
		$this->assertSame('test1234567890', $userinfo->activation);
		$this->assertTrue($userinfo->block);
		$this->assertSame($foo , $userinfo->groups);
		$this->assertSame($foo , $userinfo->groupnames);
		$this->assertSame(10000, $userinfo->registerDate);
		$this->assertSame(10000, $userinfo->lastvisitDate);
		$this->assertSame('en-GB', $userinfo->language);
		$this->assertSame('costume field', $userinfo->costume);
	}

	public function test___isset() {
		$userinfo = new Userinfo('plugin');
		$userinfo->userid = 1000;
		$this->assertTrue(isset($userinfo->userid));
	}

	public function test_getJname() {
		$userinfo = new Userinfo('plugin');
		$this->assertSame('plugin', $userinfo->getJname());
	}

	public function test_toObject() {
		$userinfo = new Userinfo('plugin');
		$object = $userinfo->toObject();

		$this->assertInstanceOf('\stdClass', $object);

		$this->assertSame('plugin', $object->jname);
	}
}
