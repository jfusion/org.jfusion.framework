<?php namespace JFusion\Plugins\mockplugin;
/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mockplugin
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

use JFusion\User\Userinfo;

/**
 * JFusion Auth Class for phpbb3
 * For detailed descriptions on these functions please check Plugin_Auth
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mockplugin
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Auth extends \JFusion\Plugin\Auth
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

	/**
	 * @param Userinfo $userinfo
	 * @return string
	 */
	function generateEncryptedPassword(Userinfo $userinfo) {
		return md5($userinfo->password_clear);
	}
}
