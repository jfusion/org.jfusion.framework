<?php namespace JFusion\Installer;
/**
 * installer model
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

use Joomla\Filesystem\Folder;
use Joomla\Event\Event;
use Joomla\Filesystem\Path;
use Joomla\Language\Text;
use Joomla\Filter\InputFilter;

use Exception;
use RuntimeException;
use SimpleXMLElement;
use stdClass;

/**
 * Installer class for JFusion plugins
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class Plugin
{
	var $manifest;

	var $name;

    /**
     * Overridden constructor
     *
     * @access    protected
     */
    function __construct()
    {
        $this->installer = new Installer;
        $this->installer->setOverwrite(true);
        $this->filterInput = new InputFilter;
    }

	/**
	 * handles JFusion plugin installation
	 *
	 * @param mixed $dir install path
	 * @param array &$result
	 *
	 * @throws \RuntimeException
	 * @return array
	 */
    function install($dir = null, &$result)
    {
        // Get a database connector object
        $db = Factory::getDBO();
        $result['status'] = false;
        $result['name'] = null;
        if (!$dir && !is_dir(Path::clean($dir))) {
            $this->installer->abort(Text::_('INSTALL_INVALID_PATH'));
	        throw new RuntimeException(Text::_('INSTALL_INVALID_PATH'));
        } else {
            $this->installer->setPath('source', $dir);

            // Get the extension manifest object
            $manifest = $this->getManifest($dir);
            if (is_null($manifest)) {
                $this->installer->abort(Text::_('INSTALL_NOT_VALID_PLUGIN'));
	            throw new RuntimeException(Text::_('INSTALL_NOT_VALID_PLUGIN'));
            } else {
                $this->manifest = $manifest;

	            $framework = Framework::getComposerInfo();

	            $version = $this->getAttribute($this->manifest, 'version');

	            /**
	             * ---------------------------------------------------------------------------------------------
	             * Manifest Document Setup Section
	             * ---------------------------------------------------------------------------------------------
	             */
	            // Set the extensions name
	            /**
	             * Check that the plugin is an actual JFusion plugin
	             */
	            $name = $this->manifest->name;
	            $name = $this->filterInput->clean($name, 'string');

	            if (!$framework || !$version || version_compare($framework->version, $version) >= 0 || $framework->version == 'dev-master') {
		            $result['name'] = $name;
		            $this->name = $name;

		            // installation path
		            $this->installer->setPath('extension_root', Framework::getPluginPath($name));
		            // get files to copy

		            /**
		             * ---------------------------------------------------------------------------------------------
		             * Filesystem Processing Section
		             * ---------------------------------------------------------------------------------------------
		             */

		            // If the plugin directory does not exist, lets create it
		            $created = false;
		            if (!file_exists($this->installer->getPath('extension_root'))) {
			            if (!$created = Folder::create($this->installer->getPath('extension_root'))) {
				            $msg = Text::_('PLUGIN') . ' ' . Text::_('INSTALL') . ': ' . Text::_('INSTALL_FAILED_DIRECTORY') . ': "' . $this->installer->getPath('extension_root') . '"';
				            $this->installer->abort($msg);
				            throw new RuntimeException($name . ': ' . $msg);
			            }
		            }
		            /**
		             * If we created the plugin directory and will want to remove it if we
		             * have to roll back the installation, lets add it to the installation
		             * step stack
		             */
		            if ($created) {
			            $this->installer->pushStep(array('type' => 'folder', 'path' => $this->installer->getPath('extension_root')));
		            }
		            // Copy all necessary files
		            if ($this->installer->parseFiles($this->manifest->files[0], -1) === false) {
			            // Install failed, roll back changes
			            $this->installer->abort();
			            throw new RuntimeException($name . ': ' . Text::_('PLUGIN') . ' ' . $name . ' ' . Text::_('INSTALL') . ': ' . Text::_('FAILED'));
		            } else {
			            $vendor = $dir . '/vendor';
			            if (is_dir(Path::clean($vendor))) {
				            Folder::copy($vendor, $this->installer->getPath('extension_root') . '/vendor', '', true);
			            }
			            $language = $dir . '/language';
			            if (is_dir(Path::clean($language))) {
				            Folder::copy($language, $this->installer->getPath('extension_root') . '/language', '', true);
			            }
			            /**
			             * ---------------------------------------------------------------------------------------------
			             * Database Processing Section
			             * ---------------------------------------------------------------------------------------------
			             */
			            //let's check to see if a plugin with the same name is already installed
			            $query = $db->getQuery(true)
				            ->select('id')
				            ->from('#__jfusion')
				            ->where('name = ' . $db->quote($name));

			            $db->setQuery($query);

			            $plugin = $db->loadObject();
			            if (!empty($plugin)) {
				            if (!$this->installer->isOverwrite()) {
					            // Install failed, roll back changes
					            $msg = Text::_('PLUGIN') . ' ' . Text::_('INSTALL') . ': ' . Text::_('PLUGIN') . ' "' . $name . '" ' . Text::_('ALREADY_EXISTS');
					            $this->installer->abort($msg);
					            throw new RuntimeException($name . ': ' . $msg);
				            } else {
					            //set the overwrite tag
					            $result['overwrite'] = 1;
				            }
			            } else {
				            //prepare the variables
				            $result['overwrite'] = 0;
				            $plugin_entry = new stdClass;
				            $plugin_entry->id = null;
				            $plugin_entry->name = $name;
				            $plugin_entry->dual_login = 0;
				            //now append the new plugin data
				            try {
					            $db->insertObject('#__jfusion', $plugin_entry, 'id');
				            } catch (Exception $e) {
					            // Install failed, roll back changes
					            $msg = Text::_('PLUGIN') . ' ' . Text::_('INSTALL') . ' ' . Text::_('ERROR') . ': ' . $e->getMessage();
					            $this->installer->abort($msg);
					            throw new RuntimeException($name . ': ' . $msg);
				            }
				            $this->installer->pushStep(array('type' => 'plugin', 'id' => $plugin_entry->id));
			            }
			            /**
			             * ---------------------------------------------------------------------------------------------
			             * Finalization and Cleanup Section
			             * ---------------------------------------------------------------------------------------------
			             */

			            //check to see if this is updating a plugin that has been copied
			            $query = $db->getQuery(true)
				            ->select('name')
				            ->from('#__jfusion')
				            ->where('original_name = ' . $db->quote($name));

			            $db->setQuery($query);
			            $copiedPlugins = $db->loadObjectList();
			            foreach ($copiedPlugins as $plugin) {
				            //update the copied version with the new files
//				            $this->copy($name, $plugin->name, true);
			            }

			            if ($result['overwrite'] == 1) {
				            $msg = Text::_('PLUGIN') . ' ' . $name . ' ' . Text::_('UPDATE') . ': ' . Text::_('SUCCESS');
			            } else {
				            $msg = Text::_('PLUGIN') . ' ' . $name . ' ' . Text::_('INSTALL') . ': ' . Text::_('SUCCESS');
			            }
			            $result['message'] = $name . ': ' . $msg;
			            $result['status'] = true;
		            }
	            } else {
		            $msg = Text::_('PLUGIN') . ' ' . $name . ': ' . Text::_('FAILED') . ' ' . Text::_('NEED_JFUSION_VERSION') . ' "' . $version . '" ' . Text::_('OR_HIGHER');
		            $this->installer->abort($msg);
		            throw new RuntimeException($name . ': ' . $msg);
	            }
            }
        }
        return $result;
    }

    /**
     * handles JFusion plugin un-installation
     *
     * @param string $name name of the JFusion plugin used
     *
     * @return array
     */
    function uninstall($name)
    {
	    $result = array();
    	$result['status'] = false;
	    try {
		    $JFusionAdmin = Factory::getAdmin($name);
		    if ($JFusionAdmin->isConfigured()) {
			    //if this plugin had been valid, call its uninstall function if it exists
			    $success = 0;
			    try {
				    list($success, $reasons) = $JFusionAdmin->uninstall();

				    $reason = '';
				    if (is_array($reasons)) {
					    $reason = implode('</li><li>' . $name . ': ', $reasons);
				    } else {
					    $reason = $name . ': ' . $reasons;
				    }
			    } catch (Exception $e) {
				    $reason = $e->getMessage();
			    }
			    if (!$success) {
				    throw new RuntimeException(Text::_('PLUGIN') . ' ' . $name . ' ' . Text::_('UNINSTALL') . ' ' . Text::_('FAILED') . ': ' . $reason);
			    }
		    }
		    $db = Factory::getDBO();

		    $query = $db->getQuery(true)
			    ->select('name , original_name')
			    ->from('#__jfusion')
			    ->where('name = ' . $db->quote($name));

		    $db->setQuery($query);
		    $plugin = $db->loadObject();

		    // delete raw
		    $query = $db->getQuery(true)
			    ->delete('#__jfusion')
			    ->where('name = ' . $db->quote($name));
		    $db->setQuery($query);
		    $db->execute();

		    $query = $db->getQuery(true)
			    ->delete('#__jfusion_users')
			    ->where('jname = ' . $db->quote($name));
		    $db->setQuery($query);
		    $db->execute();

		    $event = new Event('onInstallerPluginUninstall');
		    $event->addArgument('name', $name);
		    Factory::getDispatcher()->triggerEvent($event);

		    if ($plugin || !$plugin->original_name) {
			    $dir = Framework::getPluginPath($name);

			    if (!$name || !is_dir(Path::clean($dir))) {
				    throw new RuntimeException(Text::_('UNINSTALL_ERROR_PATH'));
			    } else {
				    /**
				     * ---------------------------------------------------------------------------------------------
				     * Remove Language files Processing Section
				     * ---------------------------------------------------------------------------------------------
				     */
				    // Get the extension manifest object
				    $manifest = $this->getManifest($dir);
				    if (is_null($manifest)) {
					    throw new RuntimeException(Text::_('INSTALL_NOT_VALID_PLUGIN'));
				    } else {
					    $this->manifest = $manifest;

					    // remove files
					    if (!Folder::delete($dir)) {
						    throw new RuntimeException(Text::_('UNINSTALL_ERROR_DELETE'));
					    } else {
						    //return success
						    $msg = Text::_('PLUGIN') . ' ' . $name . ' ' . Text::_('UNINSTALL') . ': ' . Text::_('SUCCESS');
						    $result['message'] = $msg;
						    $result['status'] = true;
					    }
				    }
			    }
		    }
	    } catch (Exception $e) {
		    $result['message'] = $name . ' ' . $e->getMessage();
		    $this->installer->abort($e->getMessage());
	    }
        return $result;
    }

	/**
	 * handles copying JFusion plugins
	 *
	 * @param string  $name     name of the JFusion plugin used
	 * @param string  $new_name name of the copied plugin
	 * @param boolean $update    mark if we updating a copied plugin
	 *
	 * @throws RuntimeException
	 * @return boolean
	 */
	function copy($name, $new_name, $update = false)
	{
		//replace not-allowed characters with _
		$new_name = preg_replace('/([^a-zA-Z0-9_])/', '_', $new_name);

		//initialise response element
		$result = array();
		$result['status'] = false;

		if ($name && $new_name) {
			//check to see if an integration was selected
			$db = Factory::getDBO();

			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__jfusion')
				->where('original_name IS NULL')
				->where('name LIKE ' . $db->quote($name));

			$db->setQuery($query);
			$record = $db->loadResult();

			$query = $db->getQuery(true)
				->select('id')
				->from('#__jfusion')
				->where('name = ' . $db->quote($new_name));

			$db->setQuery($query);
			$exsist = $db->loadResult();
			if ($exsist) {
				throw new RuntimeException($new_name . ' ' . Text::_('ALREADY_IN_USE'));
			} else if ($record) {
				$JFusionPlugin = Factory::getAdmin($name);
				if ($JFusionPlugin->multiInstance()) {
					$db = Factory::getDBO();
					if (!$update) {
						//add the new entry in the JFusion plugin table
						$query = $db->getQuery(true)
							->select('*')
							->from('#__jfusion')
							->where('name = ' . $db->quote($name));

						$db->setQuery($query);
						$plugin_entry = $db->loadObject();
						$plugin_entry->name = $new_name;
						$plugin_entry->id = null;
						//only change the original name if this is not a copy itself
						if (empty($plugin_entry->original_name)) {
							$plugin_entry->original_name = $name;
						}
						$db->insertObject('#__jfusion', $plugin_entry, 'id');
					}
					$result['message'] = $new_name . ': ' . Text::_('PLUGIN') . ' ' . $name . ' ' . Text::_('COPY') . ' ' . Text::_('SUCCESS');
					$result['status'] = true;
				} else {
					throw new RuntimeException(Text::_('CANT_COPY'));
				}
			} else {
				throw new RuntimeException(Text::_('INVALID_SELECTED'));
			}
		} else {
			throw new RuntimeException(Text::_('NONE_SELECTED'));
		}
		return $result;
	}

    /**
     * load manifest file with installation information
     *
     * @param string $dir Directory
     *
     * @return SimpleXMLElement object (or null)
     */
    function getManifest($dir)
    {
        $file = $dir . '/jfusion.xml';
        $this->installer->setPath('manifest', $file);
        // If we cannot load the xml file return null

	    $xml = Framework::getXml($file);

		if (!($xml instanceof SimpleXMLElement) || ($xml->getName() != 'extension')) {
            // Free up xml parser memory and return null
            unset($xml);
            $xml = null;
        } else {
            /**
             * Check that the plugin is an actual JFusion plugin
             */
            $type = $this->getAttribute($xml, 'type');

            if ($type !== 'jfusion') {
                //Free up xml parser memory and return null
                unset ($xml);
                $xml = null;
            }
        }

        // Valid manifest file return the object
        return $xml;
    }

    /**
     * get files function
     *
     *  @param string $folder folder name
     *  @param string $name  name
     *
     *  @return array files
     */
    function getFiles($folder, $name)
    {
        $filesArray = array();
        $files = Folder::files($folder, null, false, true);

	    $path = Framework::getPluginPath($name);

        foreach ($files as $file) {
            $file = str_replace($path . DIRECTORY_SEPARATOR, '', $file);
            $data = file_get_contents($file);
            $filesArray[] = array('name' => $file, 'data' => $data);
        }
        $folders = Folder::folders($folder, null, false, true);
        if (!empty($folders)) {
            foreach ($folders as $f) {
                $filesArray = array_merge($filesArray, $this->getFiles($f, $name));
            }
        }
        return $filesArray;
    }

    /**
     * getAttribute
     *
     *  @param SimpleXMLElement $xml xml object
     *  @param string $attribute attribute name
     *
     *  @return string result
     */
    function getAttribute($xml, $attribute)
    {
        if($xml instanceof SimpleXMLElement) {
	        $attributes = $xml->attributes();
	        if (isset($attributes[$attribute])) {
		        $xml = (string)$attributes[$attribute];
	        } else {
		        $xml = null;
	        }
        } else {
            $xml = null;
        }
        return $xml;
    }
}
