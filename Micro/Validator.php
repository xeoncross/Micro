<?php

namespace Micro;

/**
 * Validator class based on anonymous functions.
 *
 * @see http://php.net/manual/en/functions.anonymous.php
 */
class Validator
{
	public $errors;

	/**
	 * Validate the given array of data using the functions set
	 *
	 * @param array $data to validate
	 * @return array
	 */
	public function __invoke(array $data)
	{
		$this->errors = array();
		foreach((array) $this as $key => $function)
		{
			if($key == 'errors') continue;

			$value = NULL;

			if(isset($data[$key]))
			{
				$value = $data[$key];
			}

			// If the callback is an array, it means the value is expected to be an array
			if(is_array($function))
			{
				$function = current($function);
				$value = (array) $value;

				foreach($value as $i => $element)
				{
					if($error = $function($element, $i, $value, $key, $this))
					{
						// There are to ways we can go with this...
						//$this->errors[$key] = $error;
						//break;

						// Better, but unexpected validation format
						$this->errors[$key][$i] = $error;
						unset($error);
					}
				}
			}
			else
			{
				$error = $function($value, $key, $this);
			}

			if($error)
			{
				$this->errors[$key] = $error;
			}
		}

		return ! $this->errors;
	}

	/**
	 * Return the validator errors
	 *
	 * @return array
	 */
	public function errors()
	{
		return $this->errors;
	}

	/**
	 * The value exists in the data as a string, matches the given regex, and
	 * is less than the max_length
	 *
	 * @param string $regex The regex to match the string
	 * @param integer $max_length The maximum lenght of the string
	 * @return boolean
	 */
	public static function string($value, $max, $min = 0)
	{
		if($value AND is_string($value) AND mb_strlen($value) < $max)
		{
			if( ! $min OR mb_strlen($value) > $min)
			{
				return TRUE;
			}
		}

		return FALSE;
	}
}