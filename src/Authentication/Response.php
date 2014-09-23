<?php namespace JFusion\Authentication;
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use JFusion\Debugger\Debugger;

use JFusion\User\Userinfo;

/**
 * Authentication response class, provides an object for storing user and error details
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       11.1
 */
class Response
{
	/**
	 *
	 */
	function __construct() {
		$this->userinfo = new Userinfo(null);
	}

	/**
	 * Response status (see status codes)
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $status = Authentication::STATUS_FAILURE;

	/**
	 * The type of authentication that was successful
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = '';

	/**
	 *  The error message
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $error_message = '';

	/**
	 * Userinfo
	 *
	 * @var    Userinfo
	 * @since  11.1
	 *
	 */
	public $userinfo = null;

	/**
	 * @var    Debugger
	 * @since  11.1
	 */
	public $debugger = null;


}
