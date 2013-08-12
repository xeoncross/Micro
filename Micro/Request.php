<?php
namespace Micro;

class Request
{
	// URL path
	public $path = NULL;

	// Requested response format
	public $format = NULL;

	// HTTP request method
	public $method = NULL;

	// RAW php://input data
	public static $rawInput = NULL;

	/**
	 * Standard HTTP request methods that an application should respond too.
	 * TRACE, PATCH, and CONNECT should not be handled at the application level.
	 */
	public static $methods = array(
		'GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS', 'CLI'
	);

	public function __construct($method, $path, $parseFormat = TRUE)
	{
		if( ! in_array($method = strtoupper($method), static::$methods))
		{
			throw new \Exception('Invalid Request Method');
		}
		
		$this->method = $method;

		if($path = parse_url($path, PHP_URL_PATH))
		{
			$this->path = rawurldecode(trim($path, '/'));

			if($parseFormat && ($format = pathinfo($this->path, PATHINFO_EXTENSION)))
			{
				$this->format = $format;
				$this->path = mb_substr($this->path, 0, - (mb_strlen($format) + 1));
			}
		}
	}

	/**
	 * Is this an AJAX request? (jQuery, Mootools, YUI, Dojo, etc...)
	 *
	 * @return boolean
	 */
	public function isAjax()
	{
		return strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Is this an AJAX request? (jQuery, Mootools, YUI, Dojo, etc...)
	 *
	 * @return boolean
	 */
	public function isCLI()
	{
		//return PHP_SAPI === 'cli';
		return $this->method === 'CLI';
	}

	/**
	 * Is this a mobile request? (Android, iOS, Blackberry, and Mobile Opera)
	 *
	 * @return boolean
	 */
	public function isMobile()
	{
		return (bool) preg_match('~(Mobi)|(webOS)|(Android)~', getenv('HTTP_USER_AGENT'));
	}

	/**
	 * Is the incoming request over a secure HTTPS connection
	 *
	 * @return boolean
	 */
	public function isSecure()
	{
		return strtolower(getenv('HTTPS')) === 'on';
	}

	/**
	 * Is the request coming from a bot or spider?
	 * Works with Googlebot, MSN, Yahoo, possibly others.
	 *
	 * @return boolean
	 */
	public function isBot()
	{
		$ua = strtolower(getenv('HTTP_USER_AGENT'));
		return (
			false !== strpos($ua, 'googlebot') ||
			false !== strpos($ua, 'msnbot') ||
			false !== strpos($ua, 'yahoo!') ||
			false !== strpos($ua, 'slurp') ||
			false !== strpos($ua, 'bot') ||
			false !== strpos($ua, 'spider')
		);
	}

	/**
	 * Is this a Flash request?
	 * 
	 * @return bool
	 */
	public function isFlash()
	{
		return (getenv('HTTP_USER_AGENT') == 'Shockwave Flash');
	}

	/**
	 * Get raw, unparsed request body (useful for PUT and POST requests with encoded body - like JSON)
	 * Exists because PHP cannot retrieve the contents of php://input more than once
	 */
	public function rawInput()
	{
		if(static::$rawInput === NULL) {
			static::$rawInput = file_get_contents('php://input');
		}

		return static::$rawInput;
	}
}