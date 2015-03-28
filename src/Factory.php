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

use JFusion\Authentication\Cookies;
use JFusion\Application\Application;
use JFusion\Plugin\Plugin;
use JFusion\Plugin\Front;
use JFusion\Plugin\Admin;
use JFusion\Plugin\Auth;
use JFusion\Plugin\User;
use JFusion\Plugin\Platform;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseFactory;
use Joomla\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Language\Language;
use Joomla\Date\Date;
use Joomla\Event\Dispatcher;

use \RuntimeException;
use \DateTimeZone;
use stdClass;

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
class Factory
{
	/**
	 * Global database object
	 *
	 * @var    DatabaseDriver
	 * @since  11.1
	 */
	public static $database = null;

	/**
	 * Global application object
	 *
	 * @var    Application
	 * @since  11.1
	 */
	public static $application = null;

	/**
	 * Global language object
	 *
	 * @var    Language
	 * @since  11.1
	 */
	public static $language = null;

	/**
	 * Container for Date instances
	 *
	 * @var    array[Date]
	 * @since  11.3
	 */
	public static $dates = array();

	/**
	 * Container for Dispatcher instances
	 *
	 * @var    Dispatcher
	 * @since  11.3
	 */
	public static $dispatcher = null;

	/**
	 * Gets an Fusion front object
	 *
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return string object for the JFusion plugin
	 */
	public static function &getNameFromInstance($instance)
	{
		static $namnes;
		if (!isset($namnes)) {
			$namnes = array();
		}
		//only create a new plugin instance if it has not been created before
		if (!isset($namnes[$instance])) {
			$db = static::getDbo();

			$query = $db->getQuery(true)
				->select('original_name')
				->from('#__jfusion')
				->where('name = ' . $db->quote($instance));

			$db->setQuery($query);
			$name = $db->loadResult();

			if (!$name) {
				$name = $instance;
			}
			$namnes[$instance] = $name;
		}
		return $namnes[$instance];
	}

	/**
	 * Gets an Fusion front object
	 *
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return Front object for the JFusion plugin
	 */
	public static function &getFront($instance)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new plugin instance if it has not been created before
		if (!isset($instances[$instance])) {
			$name = static::getNameFromInstance($instance);

			static::pluginAutoLoad($name);

			$class = '\JFusion\Plugins\\' . $name . '\Front';
			if (!class_exists($class)) {
				$class = '\JFusion\Plugin\Front';
			}
			$instances[$instance] = new $class($instance);
		}
		return $instances[$instance];
	}
	/**
	 * Gets an Fusion front object
	 *
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return Admin object for the JFusion plugin
	 */
	public static function &getAdmin($instance)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new plugin instance if it has not been created before
		if (!isset($instances[$instance])) {
			$name = static::getNameFromInstance($instance);

			static::pluginAutoLoad($name);

			$class = '\JFusion\Plugins\\' . $name . '\Admin';
			if (!class_exists($class)) {
				$class = '\JFusion\Plugin\Admin';
			}
			$instances[$instance] = new $class($instance);
		}
		return $instances[$instance];
	}

	/**
	 * Gets an Authentication Class for the JFusion Plugin
	 *
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return Auth JFusion Authentication class for the JFusion plugin
	 */
	public static function &getAuth($instance)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new authentication instance if it has not been created before
		if (!isset($instances[$instance])) {
			$name = static::getNameFromInstance($instance);

			static::pluginAutoLoad($name);

			$class = '\JFusion\Plugins\\' . $name . '\Auth';
			if (!class_exists($class)) {
				$class = '\JFusion\Plugin\Auth';
			}
			$instances[$instance] = new $class($instance);
		}
		return $instances[$instance];
	}

	/**
	 * Gets an User Class for the JFusion Plugin
	 *
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return User JFusion User class for the JFusion plugin
	 */
	public static function &getUser($instance)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new user instance if it has not been created before
		if (!isset($instances[$instance])) {
			$name = static::getNameFromInstance($instance);

			static::pluginAutoLoad($name);

			$class = '\JFusion\Plugins\\' . $name . '\User';
			if (!class_exists($class)) {
				$class = '\JFusion\Plugin\User';
			}
			$instances[$instance] = new $class($instance);
		}
		return $instances[$instance];
	}

	/**
	 * Gets a Forum Class for the JFusion Plugin
	 *
	 * @param string $platform
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return Platform JFusion Thread class for the JFusion plugin
	 */
	public static function &getPlatform($platform, $instance)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}

		$platform = ucfirst(strtolower($platform));

		//only create a new thread instance if it has not been created before
		if (!isset($instances[$platform][$instance])) {
			$name = static::getNameFromInstance($instance);

			static::pluginAutoLoad($name);

			$class = '\JFusion\Plugins\\' . $name . '\Platform\\' . $platform . '\\Platform';
			if (!class_exists($class)) {
				$class = '\JFusion\Plugin\Platform\\' . $platform;
			}
			if (!class_exists($class)) {
				$class = '\JFusion\Plugin\Platform';
			}
			$instances[$platform][$instance] = new $class($instance);
		}
		return $instances[$platform][$instance];
	}

	/**
	 * Gets a Helper Class for the JFusion Plugin which is only used internally by the plugin
	 *
	 * @param string $instance name of the JFusion plugin used
	 *
	 * @return Plugin|false JFusion Helper class for the JFusion plugin
	 */
	public static function &getHelper($instance)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new thread instance if it has not been created before
		if (!isset($instances[$instance])) {
			$name = static::getNameFromInstance($instance);

			static::pluginAutoLoad($name);

			$class = '\JFusion\Plugins\\' . $name . '\Helper';
			if (!class_exists($class)) {
				$instances[$instance] = false;
			} else {
				$instances[$instance] = new $class($instance);
			}
		}
		return $instances[$instance];
	}

	/**
	 * @param $name
	 */
	public static function pluginAutoLoad($name)
	{
		$path = Framework::getPluginPath($name);
		if (file_exists($path . '/autoload.php')) {
			include_once($path . '/autoload.php');
		}
		if (file_exists($path . '/vendor/autoload.php')) {
			include_once($path . '/vendor/autoload.php');
		}
	}

	/**
	 * Gets an Database Connection for the JFusion Plugin
	 *
	 * @param string $jname name of the JFusion plugin used
	 *
	 * @return DatabaseDriver Database connection for the JFusion plugin
	 * @throws  RuntimeException
	 */
	public static function &getDatabase($jname)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new database instance if it has not been created before
		if (!isset($instances[$jname])) {
			/**
			 * TODO: MUST BE CHANGED! as do not rely on joomla_int
			 */
			if ($jname == 'joomla_int') {
				$db = self::getDBO();
			} else {
				//get config values
				$params = static::getParams($jname);
				//prepare the data for creating a database connection
				$host = $params->get('database_host');
				$user = $params->get('database_user');
				$password = $params->get('database_password');
				$database = $params->get('database_name');
				$prefix = $params->get('database_prefix', '');
				$driver = $params->get('database_type');
				$charset = $params->get('database_charset', 'utf8');
				//added extra code to prevent error when $driver is incorrect

				$options = array('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

				$db = DatabaseFactory::getInstance()->getDriver($driver, $options);

				if ($driver != 'sqlite') {
					//add support for UTF8
					$db->setQuery('SET names ' . $db->quote($charset));
					$db->execute();
				}

				//get the debug configuration setting
				$db->setDebug(Config::get()->get('debug'));
			}
			$instances[$jname] = $db;
		}
		return $instances[$jname];
	}

	/**
	 * Gets an Parameter Object for the JFusion Plugin
	 *
	 * @param string  $jname name of the JFusion plugin used
	 * @param boolean $reset switch to force a recreate of the instance
	 *
	 * @return Registry JParam object for the JFusion plugin
	 */
	public static function &getParams($jname, $reset = false)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		//only create a new parameter instance if it has not been created before
		if (!isset($instances[$jname]) || $reset) {
			$db = self::getDBO();

			$query = $db->getQuery(true)
				->select('params')
				->from('#__jfusion')
				->where('name = ' . $db->quote($jname));

			$db->setQuery($query);
			$params = $db->loadResult();
			$instances[$jname] = new Registry($params);
		}
		return $instances[$jname];
	}

	/**
	 * returns array of plugins depending on the arguments
	 *
	 * @param string $criteria the type of plugins to retrieve Use: master | slave | both
	 * @param string|boolean $exclude should we exclude joomla_int
	 * @param int $status only plugins with status equal or higher.
	 *
	 * @return array|stdClass plugin details
	 */
	public static function getPlugins($criteria = 'both', $exclude = false, $status = 2)
	{
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}
		$db = self::getDBO();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__jfusion');

		$key = $criteria . '_' . $exclude . '_' . $status;
		if (!isset($instances[$key])) {
			if ($exclude !== false) {
				$query->where('name NOT LIKE ' . $db->quote($exclude));
			}
			$query->where('status >= ' . (int)$status);
			$query->order('ordering');

			$db->setQuery($query);
			$list = $db->loadObjectList();

			switch ($criteria) {
				case 'slave':
					if (isset($list[0])) {
						unset($list[0]);
					}
					break;
				case 'master':
					if (isset($list[0])) {
						$list = $list[0];
					}
					break;
			}
			$instances[$key] = $list;
		}
		return $instances[$key];
	}

	/**
	 * Gets the jnode_id for the JFusion Plugin
	 * @param string $jname name of the JFusion plugin used
	 * @return string jnodeid for the JFusion Plugin
	 */
	public static function getPluginNodeId($jname) {
		$params = static::getParams($jname);
		$source_url = $params->get('source_url');
		return strtolower(rtrim(parse_url($source_url, PHP_URL_HOST) . parse_url($source_url, PHP_URL_PATH), '/'));
	}
	/**
	 * Gets the plugin name for a JFusion Plugin given the jnodeid
	 * @param string $jnode_id jnodeid to use
	 *
	 * @return string jname name for the JFusion Plugin, empty if no plugin found
	 */
	public static function getPluginNameFromNodeId($jnode_id) {
		$result = '';
		//$jid = $jnode_id;
		$plugins = static::getPlugins('both', false);
		foreach($plugins as $plugin) {
			$id = rtrim(static::getPluginNodeId($plugin->name), '/');
			if (strcasecmp($jnode_id, $id) == 0) {
				$result = $plugin->name;
				break;
			}
		}
		return $result;
	}

	/**
	 * Gets an JFusion cross domain cookie object
	 *
	 * @return Cookies object for the JFusion cookies
	 */
	public static function &getCookies() {
		static $instance;
		//only create a new plugin instance if it has not been created before
		if (!isset($instance)) {
			$instance = new Cookies(Config::get()->get('apikey'));
		}
		return $instance;
	}

	/**
	 * Get a database object.
	 *
	 * Returns the global {@link Driver} object, only creating it if it doesn't already exist.
	 *
	 * @return  DatabaseDriver
	 */
	public static function getDbo()
	{
		if (!self::$database)
		{
			//get config values

			$host = Config::get()->get('database.host');
			$user = Config::get()->get('database.user');
			$password = Config::get()->get('database.password');
			$database = Config::get()->get('database.name');
			$prefix = Config::get()->get('database.prefix');
			$driver = Config::get()->get('database.driver');
			$debug = Config::get()->get('database.debug');

			//added extra code to prevent error when $driver is incorrect

			$options = array('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

			self::$database = DatabaseFactory::getInstance()->getDriver($driver, $options);

			//get the debug configuration setting
			self::$database->setDebug(Config::get()->get('debug'));
		}
		return self::$database;
	}

	/**
	 * Get a language object.
	 *
	 * Returns the global {@link JLanguage} object, only creating it if it doesn't already exist.
	 *
	 * @return  Language object
	 *
	 * @see     Language
	 * @since   11.1
	 */
	public static function getLanguage()
	{
		if (!self::$language)
		{
			$locale = Config::get()->get('language.language');
			$debug = Config::get()->get('language.debug');
			self::$language = Language::getInstance($locale, $debug);

			Text::setLanguage(self::$language);
		}
		return self::$language;
	}

	/**
	 * Return the {@link JDate} object
	 *
	 * @param   mixed  $time      The initial time for the JDate object
	 * @param   mixed  $tzOffset  The timezone offset.
	 *
	 * @return  Date object
	 *
	 * @see     Date
	 * @since   11.1
	 */
	public static function getDate($time = 'now', $tzOffset = null)
	{
		static $classname;
		static $mainLocale;

		$language = self::getLanguage();
		$locale = $language->getTag();

		if (!isset($classname) || $locale != $mainLocale)
		{
			// Store the locale for future reference
			$mainLocale = $locale;

			if ($mainLocale !== false)
			{
				$classname = str_replace('-', '_', $mainLocale) . 'Date';

				if (!class_exists($classname))
				{
					// The class does not exist, default to JDate
					$classname = 'Date';
				}
			}
			else
			{
				// No tag, so default to JDate
				$classname = 'Date';
			}
		}

		$key = $time . '-' . ($tzOffset instanceof DateTimeZone ? $tzOffset->getName() : (string) $tzOffset);

		if (!isset(self::$dates[$classname][$key]))
		{
			self::$dates[$classname][$key] = new $classname($time, $tzOffset);
		}

		$date = clone self::$dates[$classname][$key];

		return $date;
	}

	/**
	 * Get a dispatcher object
	 *
	 * Returns the global {@link Dispatcher} object, only creating it if it doesn't already exist.
	 *
	 * @return  Dispatcher
	 *
	 * @see     Dispatcher
	 */
	public static function getDispatcher()
	{
		if (!self::$dispatcher)
		{
			self::$dispatcher = new Dispatcher();
		}
		return self::$dispatcher;
	}
}
