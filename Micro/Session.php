<?php

namespace Micro;

/**
 * Session class using encrypted cookies
 *
 * @todo We are sending duplicate cookie headers with this approach :(
 *
 * @see \Micro\Cookie
 */
class Session
{
	public $array;

	/**
	 * Create, save, and start a new session handler instance
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;

		session_set_save_handler(
			array($this, 'justSmileAndWave'),
			array($this, 'justSmileAndWave'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'justSmileAndWave'),
			array($this, 'justSmileAndWave')
		);

		// Set the session to be the same as the session data cookie we make
		// This allows us to overwrite it and only have one cookie.
		session_set_cookie_params( 
			$config['expires'],
			$config['path'], 
			$config['domain'], 
			$config['secure'], 
			$config['httponly'] 
		);

		// the following prevents unexpected effects when using objects as save handlers
		register_shutdown_function('session_write_close');

		// Start
		session_start();
	}

	/**
	 * We are *not* using a database, filesystem, or memcached instance requiring lots
	 * of setup, take-down, or cleanup. So, http://www.youtube.com/watch?v=DvYBZRwwGB4
	 */
	public function justSmileAndWave()
	{
		return true;
	}

	/**
	 * Fetch the session data from our cookie
	 *
	 * @param integer $id
	 * @return array
	 */
	public function read($id)
	{
		return Cookie::get($this->config['name'], $this->config);
	}

	/**
	 * Save the session data to the cookie
	 *
	 * @param integer $id
	 * @param array $data
	 * @return boolean
	 */
	public function write($id, $data)
	{
		return Cookie::set($this->config['name'], $data, $this->config);
	}
}