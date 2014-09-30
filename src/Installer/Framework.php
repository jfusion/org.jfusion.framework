<?php namespace JFusion\Installer;
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use JFusion\Factory;
use Joomla\Language\Text;
use Joomla\Registry\Registry;
use stdClass;

/**
 * Joomla base installer class
 *
 * @package     Joomla.Libraries
 * @subpackage  Installer
 * @since       3.1
 */
class Framework
{
	/**
	 * @return bool
	 */
	public static function install() {
		//see if we need to create SQL tables
		$db = Factory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS #__jfusion_users (
						id int(11) NOT null,
						username varchar(50),
						PRIMARY KEY (id)
					) DEFAULT CHARACTER SET utf8;';
		$db->setQuery($query);
		$db->execute();

		//create the jfusion_sync table if it does not exist already
		$query = 'CREATE TABLE IF NOT EXISTS #__jfusion_sync (
						syncid varchar(10),
						action varchar(255),
						active int(1) NOT NULL DEFAULT 0,
						syncdata longblob,
						time_start int(8),
						time_end int(8),
						PRIMARY KEY  (syncid)
				    );';
		$db->setQuery($query);
		$db->execute();

		//create the jfusion_sync_log table if it does not exist already
		$query = 'CREATE TABLE IF NOT EXISTS #__jfusion_sync_details (
						id int(11) NOT NULL auto_increment,
						syncid varchar(10),
						jname varchar(255),
						username varchar(255),
						email varchar(255),
						action varchar(255),
						`message` text,
						data longblob,
						PRIMARY KEY  (id)
				    );';
		$db->setQuery($query);
		$db->execute();

		$query = 'CREATE TABLE IF NOT EXISTS #__jfusion (
				        id int(11) NOT null auto_increment,
				        name varchar(50) NOT null,
				        params text,
				        master tinyint(4) NOT null,
				        slave tinyint(4) NOT null,
				        status tinyint(4) NOT null,
				        dual_login tinyint(4) NOT null,
				        check_encryption tinyint(4) NOT null,
				        original_name varchar(50) null,
				        ordering tinyint(4),
				        PRIMARY KEY  (id)
					);';
		$db->setQuery($query);
		$db->execute();

		$query = 'CREATE TABLE IF NOT EXISTS `#__jfusion_settings` (
						`key` varchar(255) NOT NULL,
						`value` text NOT NULL
					) DEFAULT CHARSET=utf8;';
		$db->setQuery($query);
		$db->execute();
		return true;
	}

	/**
	 * method to update the component
	 *
	 * @return boolean
	 */
	public static function update()
	{
		$results = array();

		$db = Factory::getDBO();

		/***
		 * UPGRADES FOR 1.1.0 Patch 2
		 ***/
		//see if the columns exists
		$query = 'SHOW COLUMNS FROM #__jfusion';
		$db->setQuery($query);
		$columns = $db->loadColumn();

		//check to see if the description column exists, if it does remove all pre 1.1.0 Beta Patch 2 columns
		if (in_array('description', $columns)) {
			$query = 'ALTER TABLE #__jfusion DROP COLUMN version, DROP COLUMN description, DROP COLUMN date, DROP COLUMN author, DROP COLUMN support';
			$db->setQuery($query);
			$db->execute();
		}
		/***
		 * UPGRADES FOR 1.1.1 Beta
		 ***/
		if (!in_array('original_name', $columns)) {
			//add the column
			$query = 'ALTER TABLE #__jfusion ADD COLUMN original_name varchar(50) null';
			$db->setQuery($query);
			$db->execute();
		}

		/***
		 * UPGRADES FOR 1.1.2 Beta
		 ***/
		//add the search and discussion columns
		if (!in_array('search', $columns)) {
			$query = 'ALTER TABLE #__jfusion
					ADD COLUMN search tinyint(4) NOT null DEFAULT 0,
					ADD COLUMN discussion tinyint(4) NOT null DEFAULT 0';
			$db->setQuery($query);
			$db->execute();
		}

		/***
		 * UPGRADES FOR 1.1.4/1.2
		 */
		$query = 'ALTER TABLE `#__jfusion_sync` CHANGE `syncdata` `syncdata` LONGBLOB null DEFAULT null';
		$db->setQuery($query);
		$db->execute();

		/***
		 * UPGRADES FOR 1.1.2 Stable
		 ***/
		//make id the primary key so that the username will be updated
		$query = 'ALTER TABLE `#__jfusion_users` DROP PRIMARY KEY, ADD PRIMARY KEY ( `id` )';
		$db->setQuery($query);
		$db->execute();

		/**
		 * UPGRADES FOR 1.5
		 */
		//add a active column for user sync
		$query = 'SHOW COLUMNS FROM #__jfusion_sync';
		$db->setQuery($query);
		$columns = $db->loadColumn();
		if (!in_array('active', $columns)) {
			$query = 'ALTER TABLE #__jfusion_sync
					ADD COLUMN active int(1) NOT null DEFAULT 0';
			$db->setQuery($query);
			$db->execute();
		}

		/**
		 * UPGRADES FOR 1.6
		 */

		//add a active column for user sync
		$query = 'SHOW COLUMNS FROM #__jfusion';
		$db->setQuery($query);
		$columns = $db->loadColumn();
		if (!in_array('ordering', $columns)) {
			$query = 'ALTER TABLE #__jfusion
            		ADD COLUMN ordering int(4)';
			$db->setQuery($query);
			$db->execute();
		}

		//remove columns
		if (in_array('activity', $columns)) {
			$query = 'ALTER TABLE #__jfusion DROP column activity';
			$db->setQuery($query);
			$db->execute();
		}
		if (in_array('search', $columns)) {
			$query = 'ALTER TABLE #__jfusion DROP column search';
			$db->setQuery($query);
			$db->execute();
		}
		if (in_array('discussion', $columns)) {
			$query = 'ALTER TABLE #__jfusion DROP column discussion';
			$db->setQuery($query);
			$db->execute();
		}

		/**
		 * for 2.0
		 */
		$query = 'ALTER TABLE #__jfusion_sync_details CHANGE  `message`  `message` TEXT';
		$db->setQuery($query);
		$db->execute();

		$query = 'SHOW COLUMNS FROM #__jfusion';
		$db->setQuery($query);
		$columns = $db->loadColumn();

		//remove the plugin_files if it exists
		if (in_array('plugin_files', $columns)) {
			//remove the column
			$query = 'ALTER TABLE #__jfusion DROP column plugin_files';
			$db->setQuery($query);
			$db->execute();
		}

		// let's update to json
		$query = $db->getQuery(true)
			->select('params, id')
			->from('#__jfusion');

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if(!empty($rows)) {
			foreach ($rows as $row) {
				if ($row->params) {
					$params = base64_decode($row->params);
					if (strpos($params, 'a:') === 0) {
						ob_start();
						$params = unserialize($params);
						ob_end_clean();
						if (is_array($params)) {
							$params = new Registry($params);
							$row->params  = $params->toString();
							$db->updateObject('#__jfusion', $row, 'id');
						}
					}
				}
			}
		}

		//cleanup unused plugins
		$query = $db->getQuery(true)
			->select('name')
			->from('#__jfusion')
			->where('(params IS NULL OR params = ' . $db->quote('') . ' OR params = ' . $db->quote('0') . ')')
			->where('status = 0')
			->where('master = 0')
			->where('slave = 0')
			->where('name NOT LIKE ' . $db->quote('joomla_int'));

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$query = $db->getQuery(true)
					->select('count(*)')
					->from('#__jfusion')
					->where('original_name LIKE ' . $db->quote($row->name));

				$db->setQuery($query);
				$copys = $db->loadResult();
				if (!$copys) {
					$model = new Plugin();
					$model->uninstall($row->name);
				}
			}
		}
		return true;
	}

	/**
	 * method to uninstall the component
	 *
	 * @return stdClass[] with status/message field for plugin uninstall info
	 */
	public static function uninstall()
	{
		$results = array();

		//see if any mods from jfusion plugins need to be removed
		$plugins = Factory::getPlugins('all', true, false);
		foreach($plugins as $plugin) {
			$model = new Plugin();
			$result = $model->uninstall($plugin->name);

			$r = new stdClass();
			$result['status'] = 1;
			$r->status = $result['status'];

			if (!$r->status) {
				$r->message = Text::_('UNINSTALL') . ' ' . $plugin->name . ' ' . Text::_('FAILED');
			} else {
				$r->message = Text::_('UNINSTALL') . ' ' . $plugin->name . ' ' . Text::_('SUCCESS');
			}

			$results[] = $r;
		}

		//remove the jfusion tables.
		$db = Factory::getDBO();
		$query = 'DROP TABLE IF EXISTS #__jfusion';
		$db->setQuery($query);
		$db->execute();

		$query = 'DROP TABLE IF EXISTS #__jfusion_sync';
		$db->setQuery($query);
		$db->execute();

		$query = 'DROP TABLE IF EXISTS #__jfusion_sync_details';
		$db->setQuery($query);
		$db->execute();

		$query = 'DROP TABLE IF EXISTS #__jfusion_users';
		$db->setQuery($query);
		$db->execute();
		return $results;
	}
}
