<?php namespace JFusion;
/**
 * Model for all jfusion related function
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

use JFusion\Application\Application;

use Joomla\Language\Text;

use Symfony\Component\Yaml\Exception\RuntimeException;

use \stdClass;
use \SimpleXMLElement;
use \Exception;

/**
 * Class for general JFusion functions
 *
 * @category  JFusion
 * @package   Models
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class Framework
{
	/**
	 * Returns the JFusion plugin name of the software that is currently the master of user management
	 *
	 * @return stdClass master details
	 */
	public static function getMaster()
	{
		static $jfusion_master;
		if (!isset($jfusion_master)) {
			$db = Factory::getDBO();

			$query = $db->getQuery(true)
				->select('*')
				->from('#__jfusion')
				->where('master = 1')
				->where('status = 1');

			$db->setQuery($query);
			$jfusion_master = $db->loadObject();
		}
		return $jfusion_master;
	}
	
	/**
	 * Returns the JFusion plugin name of the software that are currently the slaves of user management
	 *
	 * @return stdClass[] slave details
	 */
	public static function getSlaves()
	{
		static $jfusion_slaves;
		if (!isset($jfusion_slaves)) {
			$db = Factory::getDBO();

			$query = $db->getQuery(true)
				->select('*')
				->from('#__jfusion')
				->where('slave = 1')
				->where('status = 1');

			$db->setQuery($query);
			$jfusion_slaves = $db->loadObjectList();
		}
		return $jfusion_slaves;
	}

    /**
     * Delete old user data in the lookup table
     *
     * @param object $userinfo userinfo of the user to be deleted
     *
     * @return string nothing
     */
    public static function removeUser($userinfo)
    {
	    /**
	     * TODO: need to be change to remove the user correctly with the new layout.
	     */
	    //Delete old user data in the lookup table
	    $db = Factory::getDBO();

	    $query = $db->getQuery(true)
		    ->delete('#__jfusion_users_plugin')
		    ->where('userid = ' . $db->quote($userinfo->userid));
	    $db->setQuery($query);

	    $db->execute();
    }

    /**
     * Check if feature exists
     *
     * @static
     * @param string $jname
     * @param string $feature feature
     *
     * @return bool
     */
    public static function hasFeature($jname, $feature) {
        $return = false;
	    $admin = Factory::getAdmin($jname);
	    $user = Factory::getUser($jname);
        switch ($feature) {
            //Admin Features
            case 'wizard':
	            $return = $admin->methodDefined('setupFromPath');
                break;
            //User Features
            case 'useractivity':
                $return = $user->methodDefined('activateUser');
                break;
            case 'duallogin':
                $return = $user->methodDefined('createSession');
                break;
            case 'duallogout':
                $return = $user->methodDefined('destroySession');
                break;
            case 'updatepassword':
                $return = $user->methodDefined('updatePassword');
                break;
            case 'updateusername':
                $return = $user->methodDefined('updateUsername');
                break;
            case 'updateemail':
                $return = $user->methodDefined('updateEmail');
                break;
            case 'updateusergroup':
                $return = $user->methodDefined('updateUsergroup');
                break;
            case 'updateuserlanguage':
                $return = $user->methodDefined('updateUserLanguage');
                break;
            case 'syncsessions':
                $return = $user->methodDefined('syncSessions');
                break;
            case 'blockuser':
                $return = $user->methodDefined('blockUser');
                break;
            case 'activateuser':
                $return = $user->methodDefined('activateUser');
                break;
            case 'deleteuser':
                $return = $user->methodDefined('deleteUser');
                break;
        }
        return $return;
    }

	/**
	 * Checks to see if a JFusion plugin is properly configured
	 *
	 * @param string  $data   file path or file content
	 * @param boolean $isFile load from file
	 *
	 * @throws \Symfony\Component\Yaml\Exception\RuntimeException
	 * @return SimpleXMLElement returns true if plugin is correctly configured
	 */
	public static function getXml($data, $isFile = true)
	{
		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile) {
			// Try to load the XML file
			$xml = simplexml_load_file($data);
		} else {
			// Try to load the XML string
			$xml = simplexml_load_string($data);
		}

		if ($xml === false) {
			$message = null;
			if ($isFile) {
				$message = Text::_('FILE') . ': ' . $data;
			}
			foreach (libxml_get_errors() as $error) {
				if ($message) {
					$message .= ' ';
				}
				$message .= ' '. Text::_('MESSAGE') . ': ' . $error->message;
			}
			throw new RuntimeException(Text::_('ERROR_XML_LOAD') . ' : ' . $message);
		}
		return $xml;
	}

	/**
	 * Raise warning function that can handle arrays
	 *
	 * @param        $type
	 * @param array|string|Exception  $message   message itself
	 * @param string $jname
	 *
	 * @return string nothing
	 */
	public static function raise($type, $message, $jname = '') {
		if (is_array($message)) {
			foreach ($message as $msgtype => $msg) {
				//if still an array implode for nicer display
				if (is_numeric($msgtype)) {
					$msgtype = $jname;
				}
				static::raise($type, $msg, $msgtype);
			}
		} else {
			$app = Application::getInstance();
			if ($message instanceof Exception) {
				$message = $message->getMessage();
			}
			if (!empty($jname)) {
				$message = $jname . ': ' . $message;
			}
			$app->enqueueMessage($message, strtolower($type));
		}
	}

	/**
	 * @param string $filename file name or url
	 *
	 * @return boolean|stdClass
	 */
	public static function getImageSize($filename) {
		$result = false;
		ob_start();

		if (strpos($filename, '://') !== false && function_exists('fopen') && ini_get('allow_url_fopen')) {
			$stream = fopen($filename, 'r');

			$rawdata = stream_get_contents($stream, 24);
			if($rawdata) {
				$type = null;
				/**
				 * check for gif
				 */
				if (strlen($rawdata) >= 10 && strpos($rawdata, 'GIF89a') === 0 || strpos($rawdata, 'GIF87a') === 0) {
					$type = 'gif';
				}
				/**
				 * check for png
				 */
				if (!$type && strlen($rawdata) >= 24) {
					$head = unpack('C8', $rawdata);
					$png = array(1 => 137, 2 => 80, 3 => 78, 4 => 71, 5 => 13, 6 => 10, 7 => 26, 8 => 10);
					if ($head === $png) {
						$type = 'png';
					}
				}
				/**
				 * check for jpg
				 */
				if (!$type) {
					$soi = unpack('nmagic/nmarker', $rawdata);
					if ($soi['magic'] == 0xFFD8) {
						$type = 'jpg';
					}
				}
				if (!$type) {
					if ( substr($rawdata, 0, 2) == 'BM' ) {
						$type = 'bmp';
					}
				}
				switch($type) {
					case 'gif':
						$data = unpack('c10', $rawdata);

						$result = new stdClass;
						$result->width = $data[8]*256 + $data[7];
						$result->height = $data[10]*256 + $data[9];
						break;
					case 'png':
						$type = substr($rawdata, 12, 4);
						if ($type === 'IHDR') {
							$info = unpack('Nwidth/Nheight', substr($rawdata, 16, 8));

							$result = new stdClass;
							$result->width = $info['width'];
							$result->height = $info['height'];
						}
						break;
					case 'bmp':
						$header = unpack('H*', $rawdata);
						// Process the header
						// Structure: http://www.fastgraph.com/help/bmp_header_format.html
						// Cut it in parts of 2 bytes
						$header = str_split($header[1], 2);
						$result = new stdClass;
						$result->width = hexdec($header[19] . $header[18]);
						$result->height = hexdec($header[23] . $header[22]);
						break;
					case 'jpg':
						$pos = 0;
						while(1) {
							$pos += 2;
							$data = substr($rawdata, $pos, 9);
							if (strlen($data) < 4) {
								break;
							}
							$info = unpack('nmarker/nlength', $data);
							if ($info['marker'] == 0xFFC0) {
								if (strlen($data) >= 9) {
									$info = unpack('nmarker/nlength/Cprecision/nheight/nwidth', $data);

									$result = new stdClass;
									$result->width = $info['width'];
									$result->height = $info['height'];
								}
								break;
							} else {
								$pos += $info['length'];
								if (strlen($rawdata) < $pos+9) {
									$rawdata .= stream_get_contents($stream, $info['length']+9);
								}
							}
						}
						break;
					default:
						/**
						 * Fallback to original getimagesize this may be slower than the original but safer.
						 */
						$rawdata .= stream_get_contents($stream);
						$temp = tmpfile();
						fwrite($temp, $rawdata);
						$meta_data = stream_get_meta_data($temp);

						$info = getimagesize($meta_data['uri']);

						if ($info) {
							$result = new stdClass;
							$result->width = $info[0];
							$result->height = $info[1];
						}
						fclose($temp);
						break;
				}
			}
			fclose($stream);
		}
		if (!$result) {
			$info = getimagesize($filename);

			if ($info) {
				$result = new stdClass;
				$result->width = $info[0];
				$result->height = $info[1];
			}
		}
		ob_end_clean();
		return $result;
	}

	/**
	 * @param $seed
	 *
	 * @return string
	 */
	public static function getHash($seed)
	{
		return md5(Factory::getConfig()->get('secret') . $seed);
	}



	/**
	 * @param string $jname
	 * @param bool   $default
	 *
	 * @return mixed;
	 */
	public static function getUserGroups($jname = '', $default = false) {
		$params = Factory::getConfig();
		$usergroups = $params->get('usergroups', false);

		if ($jname) {
			if (isset($usergroups->{$jname})) {
				$usergroups = $usergroups->{$jname};

				if ($default) {
					if (isset($usergroups[0])) {
						$usergroups = $usergroups[0];
					} else {
						$usergroups = null;
					}
				}
			} else {
				if ($default) {
					$usergroups = null;
				} else {
					$usergroups = array();
				}
			}

		}
		return $usergroups;
	}

	/**
	 * @return stdClass
	 */
	public static function getUpdateUserGroups() {
		$params = Factory::getConfig();
		$usergroupmodes = $params->get('updateusergroups', new stdClass());
		return $usergroupmodes;
	}

	/**
	 * returns true / false if the plugin is in advanced usergroup mode or not...
	 *
	 * @param string $jname plugin name
	 *
	 * @return boolean
	 */
	public static function updateUsergroups($jname) {
		$updateusergroups = static::getUpdateUserGroups();
		$advanced = false;
		if (isset($updateusergroups->{$jname}) && $updateusergroups->{$jname}) {
			$master = Framework::getMaster();
			if ($master->name != $jname) {
				$advanced = true;
			}
		}
		return $advanced;
	}

	/**
	 * @static
	 *
	 * @return string
	 */
	public static function getNodeID()
	{
		$params = Factory::getConfig();
		$url = $params->get('url');
		return strtolower(rtrim(parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH), '/'));
	}

	/**
	 * @static
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function getPluginPath($name = null)
	{
		$params = Factory::getConfig();
		$path = $params->get('plugin-path');
		if ($name != null) {
			$path = $path . '/' . $name;
		}
		return $path;
	}

	/**
	 * @param $libery
	 *
	 * @return stdClass|null
	 */
	public static function getComposerInfo($libery = 'jfusion/framework')
	{
		$lib = null;
		$installed = __DIR__ . '/../../../composer/installed.json';
		if (file_exists($installed)) {
			$json = json_decode(file_get_contents($installed));
			foreach($json as $node) {
				if ($node->name === $libery) {
					$lib = $node;
					break;
				}
			}
		}
		return $lib;
	}

	/**
	 * @param string $url
	 * @param bool   $moved
	 */
	public static function redirect($url, $moved = false)
	{
		// If the headers have already been sent we need to send the redirect statement via JavaScript.
		if (headers_sent()) {
			echo "<script>document.location.href='" . str_replace("'", "&apos;", $url) . "';</script>\n";
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
			// We have to use a JavaScript redirect here because MSIE doesn't play nice with utf-8 URLs.
			if ((stripos($agent, 'MSIE') !== false || stripos($agent, 'Trident') !== false) && !(preg_match('/(?:[^\x00-\x7F])/', $url) !== 1)) {
				$html = '<html><head>';
				$html .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
				$html .= '<script>document.location.href=\'' . str_replace("'", "&apos;", $url) . '\';</script>';
				$html .= '</head><body></body></html>';

				echo $html;
			} else {
				// All other cases use the more efficient HTTP header for redirection.
				header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
				header('Location: ' . $url);
				header('Content-Type: text/html; charset=utf-8');
			}
		}
		exit();
	}
}