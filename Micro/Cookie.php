<?php

namespace Micro;

/**
 * Handle reading and writing encrypted cookies
 */
class Cookie
{
	/**
	 * Create a new encrypted cookie object
	 *
	 * @param array $config values
	 */
	public function __construct(array $config)
	{
		if(empty($config['key'])) {
			throw new \Exception('No cookie encryption key is set');
		}

		$this->config = $config + array(
			'timeout' => 60 * 30, // Only last for half an hour
			'expires' => 0, // Expires when browser closes
			'path' => '/',
			'domain' => '.' . getenv('HTTP_HOST'),
			'secure' => false,
			'httponly' => true
		);
	}

	/**
	 * Decrypt and fetch cookie data as long as the cookie has not expired
	 *
	 * @param string $name of cookie
	 * @param array $config settings
	 * @return mixed
	 */
	public function get($name)
	{
		if(isset($_COOKIE[$name]))
		{
			if($value = json_decode(Cipher::decrypt($_COOKIE[$name], $this->config['key']), TRUE))
			{
				if($value[0] < (time() + $this->config['timeout']))
				{
					return $value[1];
				}
			}
		}
	}

	/**
	 * Called before any output is sent to create an encrypted cookie with the given value.
	 *
	 * @param string $key cookie name
	 * @param mixed $value to save
	 * @param array $config settings
	 * return boolean
	 */
	public function set($name, $value, array $config = array())
	{
		extract($this->config);

		// If the cookie is being removed we want it left blank
		if($value)
		{
			$value = Cipher::encrypt(json_encode(array(time(), $value)), $key);
		}

		// Update the current cookie global
		$_COOKIE[$name] = $value;

		// Save cookie to user agent
		setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
	}
}