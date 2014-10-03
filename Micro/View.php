<?php
namespace Micro;

/**
 * Provide template inheritance to HTML, XML, or other text-based documents
 */
class View
{
	public $__view, $__blocks, $__append;
	public static $ext = '.php', $directory = 'View/';

	public function __construct($file)
	{
		$this->__view = trim($file, '/');
	}

	/**
	 * Return the view's HTML
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->load($this->__view);
		} catch(\Exception $exception) {
			return '' . $exception;
		}
	}

	/**
	 * Load the given view
	 *
	 * @param string $__file__ the filename to load
	 * @return string
	 */
	public function load($__file__)
	{
		ob_start();
		extract((array) $this);
		require static::$directory . $__file__. static::$ext;
		$result = ob_get_clean();

		if(isset($this->extends)) {
			$extends = $this->extends;
			$this->extends = null;
			
			// If content wasn't defined
			if(empty($this->__blocks['content'])) {
				$this->__blocks['content'] = $result;
			}

			return $this->load($extends);
		}

		return $result;
	}

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
		foreach($values as $key => $value) {
			$this->$key = $value;
		}

		return $this;
	}

	/**
	 * Empty default block to be extended by child templates
	 *
	 * @param string $name of block
	 * @param string $default Default value to return if block is missing
	 * @return string
	 */
	public function block($name, $keep = false, $default = '')
	{
		if(isset($this->__blocks[$name])) {
			$block = $this->__blocks[$name];
			if( ! $keep) { unset($this->__blocks[$name]); }
			return $block;
		}

		return $default;
	}

	/**
	 * Start a new block
	 */
	public function start()
	{
		ob_start();
	}

	/**
	 * End a block. The last item in the chain is the parent block.
	 *
	 * @param string $name name of block
	 * @param string $append Set to 'append', 'prepend', or default false
	 */
	public function end($name, $append = false)
	{
		$buffer = ob_get_clean();

		if(empty($this->__blocks[$name])) {

			$this->__blocks[$name] = trim($buffer);

			$this->__append[$name] = $append ? ($append === 'prepend' ? 1 : 2) : false;

		} elseif ( ! empty($this->__append[$name])) {

			if($this->__append[$name] === 1) {
				$this->__blocks[$name] .= $buffer;
			} else {
				$this->__blocks[$name] = $buffer . $this->__blocks[$name];
			}

			$this->__append[$name] = $append ? ($append === 'prepend' ? 1 : 2) : false;
		}

		print $this->block($name, true);
	}

	/**
	 * Convert special characters to HTML safe entities.
	 *
	 * @param string $string to encode
	 * @return string
	 */
	public function escape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Convert dangerous HTML entities into special characters
	 *
	 * @param string $s string to decode
	 * @return string
	 */
	public function decode($string)
	{
		return htmlspecialchars_decode($string, ENT_QUOTES, 'UTF-8');
	}

}