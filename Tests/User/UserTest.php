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

use JFusion\Factory;
use JFusion\Framework;
use JFusion\Tests\Abstracts\FrameworkTestCase;
use JFusion\User\Sync;
use JFusion\User\Userinfo;
use Joomla\Language\Text;

use Joomla\Registry\Registry;
use Psr\Log\LogLevel;
use RuntimeException;

use stdClass;
use Exception;

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
class UserTest extends FrameworkTestCase
{
	public function test_getInstance() {
		$this->markTestSkipped('skipping: getInstance');
	}

	public function test_login() {
		$this->markTestSkipped('skipping: login');
	}

	public function test_logout() {
		$this->markTestSkipped('skipping: logout');
	}

	public function test_delete() {
		$this->markTestSkipped('skipping: delete');
	}

	public function test_save() {
		$this->markTestSkipped('skipping: save');
	}
}