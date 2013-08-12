<?php
namespace Micro;

/**
 * Provide template inheritance to HTML, XML, or other text-based documents
 */
class View
{
	public $__blocks, $__append;
	public static $ext = '.php';

	/**
	 * Allows setting template values while still returning the object instance
	 * $view->title($title)->text($text);
	 *
	 * @return this
	 */
	public function __call($key, $args)
	{
		$this->$key = $args[0];
		return $this;
	}

	/**
	 * Set an array of template values
	 *
	 * @param array $values
	 * @return this
	 */
	public function set(array $values)
	{
		foreach($values as $key => $value)
		{
			$this->$key = $value;
		}

		return $this;
	}

	/**
	 * Render template HTML
	 *
	 * @param string $file the template file to load
	 * @return string
	 */
	public function __invoke($file, $dir = __DIR__)
	{
		extract((array) $this);
		ob_start();
		require rtrim($dir, '/') . '/' . $file . static::$ext;
		return trim(ob_get_clean());
	}

	/**
	 * Extend a parent template
	 *
	 * @param string $file name of template
	 */
	public function extend($file, $dir = __DIR__)
	{
		ob_end_clean(); // Ignore this child class and load the parent!
		ob_start();
		print $this($file, $dir);
	}

	/**
	 * Start a new block
	 */
	public function start()
	{
		ob_start();
	}

	/**
	 * Empty default block to be extended by child templates
	 *
	 * @param string $name of block
	 * @param string $default Default value to return if block is missing
	 * @return string
	 */
	public function block($name, $default = '')
	{
		if(isset($this->__blocks[$name]))
		{
			return $this->__blocks[$name];
		}

		return $default;
	}

	/**
	 * End a block
	 *
	 * @param string $name name of block
	 * @param boolean $keep_parent true to append parent block contents
	 */
	public function end($name, $keep_parent = FALSE)
	{
		$buffer = ob_get_clean();

		if( ! isset($this->__blocks[$name]))
		{
			$this->__blocks[$name] = $buffer;
			if($keep_parent) $this->__append[$name] = TRUE;
		}
		elseif(isset($this->__append[$name]))
		{
			if( ! $keep_parent) unset($this->__append[$name]);
			$this->__blocks[$name] = $buffer . $this->__blocks[$name];
		}
		else
		{
			$this->__blocks[$name] = $buffer;
		}

		print $this->__blocks[$name];
	}
}