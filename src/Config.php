<?php namespace JFusion;
/**
 * Factory model that can generate any jfusion objects or classes
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

use Exception;
use Joomla\Registry\Registry;
use \RuntimeException;

/**
 * Custom parameter class that can save array values
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */

/**
 * Singleton static only class that creates instances for each specific JFusion plugin.
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class Config
{
	/**
	 * Global configuration object
	 *
	 * @var    Registry
	 * @since  11.1
	 */
	public static $config = null;

	/**
	 * Get a configuration object
	 *
	 * Returns the global {@link Registry} object, only creating it if it doesn't already exist.
	 *
	 * @throws \RuntimeException
	 * @return  Registry
	 *
	 * @see     Registry
	 * @since   11.1
	 */
	public static function get()
	{
		if (!self::$config instanceof Registry) {
			throw new RuntimeException('NO_CONFIG');
		}
		return self::$config;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns the global {@link Registry} object, only creating it if it doesn't already exist.
	 *
	 * @param Registry $config

	 * @see     Registry
	 * @since   11.1
	 */
	public static function set(Registry $config)
	{
		self::$config = $config;
	}

	/**
	 * @param bool $overwrite
	 */
	public static function load($overwrite = true)
	{
		if (!self::$config instanceof Registry) {
			throw new RuntimeException('NO_CONFIG');
		} else {
			try {
				$db = Factory::getDbo();

				$query = $db->getQuery(true)
					->select('*')
					->from('#__jfusion_settings');

				$db->setQuery($query);
				$settings = $db->loadObjectList();

				foreach ($settings as $setting) {
					if (!self::$config->exists($setting->key) || $overwrite) {
						$value = json_decode($setting->value);
						self::$config->set($setting->key, $value);
					}
				}
			} catch (Exception $e) {
			}
		}
	}

	/**
	 * @param string $key
	 * @param $value
	 */
	public static function saveKey($key, $value)
	{
		if (!self::$config instanceof Registry) {
			throw new RuntimeException('NO_CONFIG');
		} else {
			self::$config->set($key, $value);

			$db = Factory::getDbo();

			$entry = new \stdClass();
			$entry->key = $key;
			$entry->value = json_encode($value);

			$query = $db->getQuery(true)
				->select($db->quoteName('key'))
				->from('#__jfusion_settings')
				->where($db->quoteName('key') . ' = ' . $db->quote($key));

			$db->setQuery($query);
			$key = $db->loadResult();

			if ($key) {
				$db->updateObject('#__jfusion_settings', $entry, 'key');
			} else {
				$db->insertObject('#__jfusion_settings', $entry, 'key');

			}
		}
	}
}
