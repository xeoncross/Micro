<?php
/**
 * Error & Exception
 *
 * Provides global error and exception handling with detailed backtraces.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Micro;

class Error
{
	/**
	 * Fetch and HTML highlight serveral lines of a file.
	 *
	 * @param string $file to open
	 * @param integer $number of line to highlight
	 * @param integer $padding of lines on both side
	 * @return string
	*/
	public static function source($file, $number, $padding = 5)
	{
		// Get lines from file
		$lines = array_slice(file($file), $number-$padding-1, $padding*2+1, 1);

		$html = '';
		foreach($lines as $i => $line)
		{
			$html .= '<b>' . sprintf('%' . mb_strlen($number + $padding) . 'd', $i + 1) . '</b> '
				. ($i + 1 == $number ? '<em>' . htmlspecialchars($line) . '</em>' : htmlspecialchars($line));
		}
		return $html;
	}


	/**
	 * Fetch a backtrace of the code
	 *
	 * @param int $offset to start from
	 * @param int $limit of levels to collect
	 * @return array
	 */
	public static function backtrace($offset, $limit = 8)
	{
		$trace = array_slice(debug_backtrace(), $offset, $limit);

		foreach($trace as $i => &$v)
		{
			if( ! isset($v['file']))
			{
				unset($trace[$i]);
				continue;
			}
			$v['source'] = self::source($v['file'], $v['line']);
		}

		return $trace;
	}

}
