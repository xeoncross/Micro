<?php

namespace Micro;

/**
 * Command line parsing and formatting class
 */
class CLI
{
	public static $options = NULL;

	/**
	 * Safely fetch a command line argument by name or position
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function arg($key, $default = NULL)
	{
		if(static::$options === NULL) {
			static::$options = static::parse();
		}

		return isset(static::$options[$key]) ? static::$options[$key] : $default;
	}

	/**
	 * Color string output for the CLI using standard color codes.
	 *
	 * @param string $text to color
	 * @param string $color of text
	 * @param string $bold True to bold the text
	 */
	public static function color($text, $color, $bold = FALSE)
	{
		$colors = array_flip(array(
			30 => 'gray', 'red', 'green', 'yellow', 'blue', 'purple', 'cyan', 'white', 'black'
		));

		return "\033[" . ($bold ? '1' : '0') . ';' . $colors[$color] . "m$text\033[0m";
	}

	/**
	 * Parse an array of command line arguments
	 *
	 * @param array $argv
	 * @return array
	 */
	public static function parse(array $argv = NULL)
	{
		$argv = $argv ?: $_SERVER['argv'];
		$options = array();

		foreach($argv as $value) {

			if($value{0} == '-') {

				list($value, $key) = explode('=', ltrim($value, '-'), 2) + array(1 => '');

				if($key) {
					$options[$value] = $key;
					continue;
				}
			}

			$options[] = $value;
		}

		return $options;
	}
}