<?php
/**
 * HTML
 *
 * Provides quick HTML snipets for common tasks
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

namespace Micro;

class HTML
{

	/**
	 * Create a gravatar <img> tag
	 *
	 * @param $email the users email address
	 * @param $size	the size of the image
	 * @param $alt the alt text
	 * @param $type the default image type to show
	 * @param $rating max image rating allowed
	 * @return string
	 */
	public static function gravatar($email = '', $size = 80, $alt = 'Gravatar', $type = 'mm', $rating = 'g')
	{
		return '<img src="http://www.gravatar.com/avatar/' . md5($email) . "?s=$size&d=$type&r=$rating\" alt=\"$alt\" />";
	}

	/**
	 * Create a gravatar <img> tag
	 *
	 * @param $email the users email address
	 * @param $size	the size of the image
	 * @param $alt the alt text
	 * @param $type the default image type to show
	 * @param $rating max image rating allowed
	 * @return string
	 */
	public static function gravatar_url($email = '', $size = 80, $type = 'mm', $rating = 'g')
	{
		return 'http://www.gravatar.com/avatar/' . md5($email) . "?s=$size&amp;d=$type&amp;r=$rating\"";
	}

	/**
	 * Compiles an array of HTML attributes into an attribute string and
	 * HTML escape it to prevent malformed (but not malicious) data.
	 *
	 * @param array $attributes the tag's attribute list
	 * @return string
	 */
	public static function attributes(array $attributes = NULL)
	{
		if( ! $attributes) return;

		asort($attributes);
		$h = '';
		foreach($attributes as $k => $v)
		{
			$h .= " $k=\"$v\"";
		}
		return $h;
	}


	/**
	 * Create an HTML Link
	 *
	 * @param string $url for the link
	 * @param string $text the link text
	 * @param array $attributes of additional tag settings
	 * @return string
	 */
	public static function link($url, $text = '', array $attributes = NULL)
	{
		if( ! $attributes)
		{
			$attributes = array();
		}

		return self::tag('a', $text, $attributes + array('href' => site_url($url)));
	}


	/**
	 * Auto creates a form select dropdown from the options given .
	 *
	 * @param string $name the select element name
	 * @param array $options the select options
	 * @param mixed $selected the selected options(s)
	 * @param array $attributes of additional tag settings
	 * @return string
	 */
	public static function select($name, array $options, $selected = NULL, array $attributes = NULL)
	{
		$h = '';
		foreach($options as $k => $v)
		{
			$a = array('value' => $k);

			// Is this element one of the selected options?
			if($selected AND in_array($k, (array)$selected))
			{
				$a['selected'] = 'selected';
			}

			$h .= self::tag('option', $v, $a);
		}

		if( ! $attributes)
		{
			$attributes = array();
		}

		return self::tag('select', $h, $attributes+array('name' => $name));
	}

	/**
	 * The magic call static method is triggered when invoking inaccessible
	 * methods in a static context. This allows us to create tags from method
	 * calls.
	 *
	 *     View::div('This is div content.', array('id' => 'myDiv'));
	 *
	 * @param string $tag The method name being called
	 * @param array $args Parameters passed to the called method
	 * @return string
	 */
	public static function __callStatic($tag, $args)
	{
		//return"\n<$tag" . self::attributes($attributes) . ($text === 0 ? ' />' : ">$text</$tag>");
		$args[1] = isset($args[1]) ? self::attributes($args[1]) : '';
		return "<$tag{$args[1]}>{$args[0]}</$tag>";
	}


	/**
	 * Create an HTML tag
	 *
	 * @param string $tag the tag name
	 * @param string $text the text to insert between the tags
	 * @param array $attributes of additional tag settings
	 * @return string
	 */
	public static function tag($tag, $text = '', array $attributes = NULL)
	{
		return"<$tag" . self::attributes($attributes) . ($text === 0 ? ' />' : ">$text</$tag>");
	}

}
