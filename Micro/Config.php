<?php

/**
 * Config
 *
 * Provides a configuration object with read-only attributes.
 *
 * @package		MicroMVC
 * @author		John P. Bloch
 * @copyright	(c) 2013 MicroMVC Framework
 * @license		http://micromvc.com/license
 * ********************************* 80 Columns *********************************
 */

namespace Micro;

class Config
{

	protected static $__files = array();
	protected $__data = array();

	public function __construct($file)
	{
		if(!empty(static::$__files[$file]))
		{
			$this->_set(static::$__files[$file]);
			return;
		}
		if(!file_exists($file)) throw new \Exception('Config file does not exist!');
		require $file;
		$config = isset($config) ? (array) $config : array();
		static::$__files[$file] = $config;
		$this->_set($config);
	}

	protected function _set(array $data)
	{
		$this->__data = $data;
	}
	
	public function __get($name)
	{
		return isset($this->__data[$name]) ? $this->__data[$name] : null;
	}
	
	public function all()
	{
		return $this->__data;
	}

}

// END
