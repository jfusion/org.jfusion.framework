<?php namespace JFusion\Tests\Abstracts;
/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 19-08-14
 * Time: 15:16
 */

use JFusion\Factory;
use JFusion\Tests\Helper\TestHelper;
use JFusion\Tests\Mock\Hook;

use Joomla\Registry\Registry;

/**
 * Abstract test case class for database testing.
 *
 * @since  1.0
 */
abstract class FrameworkTestCase extends \PHPUnit_Extensions_Database_TestCase
{
	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function setUpBeforeClass()
	{
		// We always want the default database test case to use an SQLite memory database.
		$options = array(
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => 'jos_'
		);


		$hook = new Hook();

		$dispatcher = Factory::getDispatcher();
		$dispatcher->addListener($hook);

		$conf = new Registry();
		$conf->set('dbtype', 'sqlite');
		$conf->set('db', ':memory:');
		$conf->set('dbprefix', 'jos_');

		$conf->set('secret', 'testing');

		$conf->set('url', 'http://localhost/path/to/framework');
		$conf->set('plugin-path', '/fake/path/to/plugins');

		$usergroups = new \stdClass();
		$usergroups->mockplugin = array(array(1), array(1, 2), array(3));
		$usergroups->mockplugin_1 = array(array(1), array(2,5), array(3));
		$conf->set('usergroups', $usergroups);

		$updateusergroups = new \stdClass();
		$updateusergroups->mockplugin = true;
		$updateusergroups->mockplugin_master = true;
		$conf->set('updateusergroups', $updateusergroups);

		Factory::$config = $conf;

		try
		{
			Factory::getDbo();

			$pdo = new \PDO('sqlite::memory:');
			$pdo->exec(file_get_contents(__DIR__ . '/../Schema/ddl.sql'));
			TestHelper::setValue(Factory::$database, 'connection', $pdo);

			$params = Factory::getParams('mockplugin');

			$params->set('database_name', ':memory:');
			$params->set('database_prefix', 'mockplugin_');
			$params->set('database_type', 'sqlite');

			$params->set('source_url', 'http://localhost/path/to/mockplugin/');

			$db1 = Factory::getDatabase('mockplugin');
			TestHelper::setValue($db1, 'connection', $pdo);


			$params = Factory::getParams('mockplugin_1');

			$params->set('database_name', ':memory:');
			$params->set('database_prefix', 'mockplugin_1_');
			$params->set('database_type', 'sqlite');

			$params->set('source_url', 'http://localhost/path/to/mockplugin_1/');

			$db2 = Factory::getDatabase('mockplugin_1');
			TestHelper::setValue($db2, 'connection', $pdo);
		} catch (\RuntimeException $e) {
			Factory::$database = null;
		}

		// If for some reason an exception object was returned set our database object to null.
		if (Factory::$database instanceof \Exception) {
			Factory::$database = null;
		}
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function tearDownAfterClass()
	{
		Factory::$database = null;
	}

	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   object  $mockObject  The mock object that the callbacks are being assigned to.
	 * @param   array   $array       An array of methods names to mock with callbacks.
	 *
	 * @return  void
	 *
	 * @note    This method assumes that the mock callback is named {mock}{method name}.
	 * @since   1.0
	 */
	public function assignMockCallbacks($mockObject, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName = $index;
				$callback = $method;
			}
			else
			{
				$methodName = $method;
				$callback = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($this->any())
				->method($methodName)
				->will($this->returnCallback($callback));
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   object  $mockObject  The mock object.
	 * @param   array   $array       An associative array of methods to mock with return values:<br />
	 *                               string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function assignMockReturns($mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($this->any())
				->method($method)
				->will($this->returnValue($return));
		}
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 *
	 * @since   1.0
	 */
	protected function getConnection()
	{
		if (!is_null(Factory::$database))
		{
			return $this->createDefaultDBConnection(Factory::$database->getConnection(), ':memory:');
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 *
	 * @since   1.0
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../Stubs/empty.xml');
	}

	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return  \PHPUnit_Extensions_Database_Operation_Composite
	 *
	 * @since   1.0
	 */
	protected function getSetUpOperation()
	{
		// Required given the use of InnoDB contraints.
		return new \PHPUnit_Extensions_Database_Operation_Composite(
			array(
				\PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL(),
				\PHPUnit_Extensions_Database_Operation_Factory::INSERT()
			)
		);
	}

	/**
	 * Returns the database operation executed in test cleanup.
	 *
	 * @return  \PHPUnit_Extensions_Database_Operation_Factory
	 *
	 * @since   1.0
	 */
	protected function getTearDownOperation()
	{
		// Required given the use of InnoDB contraints.
		return \PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		if (empty(Factory::$database))
		{
			$this->markTestSkipped();
		}

		parent::setUp();
	}
}