<?php namespace JFusion\Plugin;

/**
 * Abstract public class for JFusion
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

/**
 * Abstract interface for all JFusion functions that are accessed through the Joomla front-end
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class Front extends Plugin
{
	var $helper;

	/**
	 * @param string $instance instance name of this plugin
	 */
	function __construct($instance)
	{
		parent::__construct($instance);
		//get the helper object
		$this->helper = & Factory::getHelper($this->getJname(), $this->getName());
	}

    /**
     * Returns the registration URL for the integrated software
     *
     * @return string registration URL
     */
    function getRegistrationURL()
    {
        return '';
    }

    /**
     * Returns the lost password URL for the integrated software
     *
     * @return string lost password URL
     */
    function getLostPasswordURL()
    {
        return '';
    }

    /**
     * Returns the lost username URL for the integrated software
     *
     * @return string lost username URL
     */
    function getLostUsernameURL()
    {
        return '';
    }
}
