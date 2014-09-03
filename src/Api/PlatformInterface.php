<?php namespace JFusion\Api;
use Joomla\Event\Event;

/**
 * Created by PhpStorm.
 * User: fanno
 * Date: 18-03-14
 * Time: 14:14
 */
interface PlatformInterface
{
	/**
	 * used for platform login
	 *
	 * @param   Event   $event
	 */
	function onPlatformLogin($event);

	/**
	 * used for platform logout
	 *
	 * @param   Event   $event
	 */
	function onPlatformLogout($event);

	/**
	 * used for platform delete user
	 *
	 * @param   Event   $event
	 */
	function onPlatformUserDelete($event);

	/**
	 * used for platform user register
	 *
	 * @param   Event   $event
	 */
	function onPlatformUserRegister($event);

	/**
	 * used for platform user update
	 *
	 * @param   Event   $event
	 */
	function onPlatformUserUpdate($event);

	/**
	 * used for platform route url
	 *
	 * @param   Event   $event
	 */
	function onPlatformRoute($event);
}