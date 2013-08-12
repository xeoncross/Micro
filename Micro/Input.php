<?php

namespace Micro;

/**
 * Safely fetch values from PHP Super Global arrays
 */
class Input
{
	/**
	 * Safely fetch a $_POST value by key name
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function post($key, $default = NULL)
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	/**
	 * Safely fetch a $_GET value by key name
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($key, $default = NULL)
	{
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	/**
	 * Safely fetch a $_COOKIE value by key name
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function cookie($key, $default = NULL)
	{
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
	}

	/**
	 * Safely fetch a command line argument by name or position
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function cli($key, $default = NULL)
	{
		return CLI::arg($key, $default);
	}

	/**
	 * Safely fetch a $_SESSION value by key name
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function session($key, $default = NULL)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}
}