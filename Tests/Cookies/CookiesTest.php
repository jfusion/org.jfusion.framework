<?php namespace JFusion\Tests\User;
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use JFusion\Cookies\Cookies;

/**
 * Test class for Authentication
 *
 * @since  1.0
 */
class CookiesTest extends \PHPUnit_Framework_TestCase
{

	public function test___construct() {
		$this->markTestSkipped();
	}

	public function test_addCookie() {
		$this->markTestIncomplete();
	}

	public function test_executeRedirect() {
		$this->markTestIncomplete();
	}

	public function test_getApiUrl() {
		$cookies = new Cookies();

		list($api, $cookiedomain) = $cookies->getApiUrl('https://site.com/baa/');
		$this->assertSame('https://site.com/baa/jfusionapi.php', $api);
		$this->assertSame('site.com/baa/', $cookiedomain);

		list($api, $cookiedomain) = $cookies->getApiUrl('http://site.com/baa/');
		$this->assertSame('http://site.com/baa/jfusionapi.php', $api);
		$this->assertSame('site.com/baa/', $cookiedomain);

		list($api, $cookiedomain) = $cookies->getApiUrl('https://site.com/');
		$this->assertSame('https://site.com/jfusionapi.php', $api);
		$this->assertSame('site.com/', $cookiedomain);

		list($api, $cookiedomain) = $cookies->getApiUrl('http://site.com/');
		$this->assertSame('http://site.com/jfusionapi.php', $api);
		$this->assertSame('site.com/', $cookiedomain);
	}

	public function test_buildCookie() {
		$this->markTestSkipped();
	}

	public function test_implodeCookies() {
		$cookies = new Cookies();

		$cookie = array('cookie1' => 'value1',
			'cookie2' => 'value2',
			'cookie3' => 'value3');

		$this->assertSame('cookie1=value1; cookie2=value2; cookie3=value3;', $cookies->implodeCookies($cookie));
	}
}
