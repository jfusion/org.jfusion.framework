<?php namespace JFusion\Plugin;

/**
 * Abstract Plugin_Platform class for JFusion
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
use stdClass;

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
class Plugin_Platform extends Plugin
{
	var $helper;

	/**
	 * @var $data stdClass
	 */
	var $data;

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
	 * framework has file?
	 *
	 * @param $file
	 *
	 * @return boolean|string
	 */
	final public function hasFile($file)
	{
		$helloReflection = new \ReflectionClass($this);
		$dir = dirname($helloReflection->getFilename());
		if(file_exists($dir . '/' . $file)) {
			return $dir . '/' . $file;
		}
		return false;
	}

	/**
	 * Called when JFusion is uninstalled so that plugins can run uninstall processes such as removing auth mods
	 * @return array    [0] boolean true if successful uninstall
	 *                  [1] mixed reason(s) why uninstall was unsuccessful
	 */
	function uninstall()
	{
		return array(true, '');
	}
}
