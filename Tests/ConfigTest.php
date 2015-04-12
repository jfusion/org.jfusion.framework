<?php namespace JFusion\Tests;
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
use JFusion\Config;
use JFusion\Tests\Abstracts\FrameworkTestCase;
use Joomla\Registry\Registry;

/**
 * Test class for Authentication
 *
 * @since  1.0
 */
class ConfigTest extends FrameworkTestCase
{
	public function test_get()
	{
		$this->assertInstanceOf('\Joomla\Registry\Registry', Config::get());
	}

	public function test_set()
	{
		$original = Config::get();

		$config = new Registry();

		Config::set($config);

		$this->assertEquals(0 , Config::get()->count());

		$config->set('test', 4);

		Config::set($config);

		$this->assertEquals(1, Config::get()->count());
		$this->assertEquals(4 , Config::get()->get('test', 2));

		Config::set($original);
	}

	public function test_load()
	{
		Config::load(true);
		$this->assertInstanceOf('\stdClass', Config::get()->get('user', null));
	}

	public function test_saveKey()
	{
		Config::saveKey('foo', 'baa');
		$this->assertEquals('baa' , Config::get()->get('foo', 2));
	}
}
