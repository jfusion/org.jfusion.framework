<?php namespace JFusion\Tests\User;
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

use JFusion\Tests\Abstracts\FrameworkTestCase;
use JFusion\User\Groups;

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
class GroupsTest extends FrameworkTestCase
{
	public function test_get() {
		$groups = Groups::get();

		$this->assertCount(3, $groups->mockplugin);
		$this->assertCount(3, $groups->mockplugin_1);

		$groups = Groups::get('mockplugin');
		$this->assertCount(3, $groups);

		$groups = Groups::get('mockplugin', true);
		$this->assertCount(1, $groups);
	}

	public function test_getUpdate() {
		$update = Groups::getUpdate();

		$this->assertTrue($update->mockplugin);
		$this->assertTrue($update->mockplugin_1);
	}

	public function test_save() {
		$groups = array();

		$groups['foo'] = array(1);
		$groups['baa'] = array(1);

		Groups::save($groups);
		Groups::$groups = null;

		$groups = Groups::get();

		$this->assertCount(1, $groups->foo);
		$this->assertCount(1, $groups->baa);
	}

	public function test_saveUpdate() {
		$update = array();

		$update['foo'] = true;
		$update['baa'] = false;

		Groups::saveUpdate($update);
		Groups::$update = null;

		$update = Groups::getUpdate();

		$this->assertTrue($update->foo);
		$this->assertFalse($update->baa);
	}

	public function test_isUpdate() {
		$update = Groups::isUpdate('foobaa');
		$this->assertFalse($update);

		$update = Groups::isUpdate('mockplugin');
		$this->assertFalse($update);

		$update = Groups::isUpdate('mockplugin_1');
		$this->assertTrue($update);
	}
}