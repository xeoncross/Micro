<?php
/**
 * Response
 *
 * Prepare the Header/Body response to send to the client
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2013 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Micro;

class Response
{
	/**
	 * HTTP Response Statuses
	 *
	 * Uncommon or server-level statuses are not included
	 */
	const OK = 200;
	const CREATED = 201;
	const MOVED_PERM = 301;
	const MOVED_TEMP = 302;
	const REDIRECT = 307;
	const BAD_REQUEST = 400;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE =  406;
	const SERVER_ERROR = 500;

	/**
	 * Mapping of integer HTTP status codes to constants
	 */
	public static $codes = array(
		self::OK => '200 OK',
		self::CREATED => '201 Created',
		self::MOVED_PERM => '301 Moved Permanently',
		self::MOVED_TEMP => '302 Found',
		self::REDIRECT => '307 Temporary Redirect',
		self::BAD_REQUEST => '400 Bad Request',
		self::FORBIDDEN => '403 Forbidden',
		self::NOT_FOUND => '404 Not Found',
		self::METHOD_NOT_ALLOWED => '405 Method Not Allowed',
		self::NOT_ACCEPTABLE => '406 Not Acceptable',
		self::SERVER_ERROR => '500 Internal Server Error'
	);

	// Need to be able to change this for Unit Testing
	public $mode = PHP_SAPI;
	public $headers = array();
	public $status = NULL;
	public $content = NULL;

	/**
	 * Create a new response object using the content and status provided.
	 * Can pass existing response objects as content.
	 *
	 * @param mixed $content the response content body or object
	 * @param string $status the HTTP response code
	 */
	public function __construct($content = NULL, $status = self::OK)
	{
		$this->status($status);
		$this->content($content);
		$this->header('Content-Type', 'text/html; charset=utf-8');
	}

	public function status($status = NULL)
	{
		if($status !== NULL) {
			$this->status = $status;
		}

		return $this->status;
	}

	public function content($content = NULL)
	{
		if($content === NULL) {	
			return $this->content;
		}

		if(is_array($content)) {
			$this->header('Content-Type', 'application/json; charset=utf-8');

		// Inherit existing response object properties
		} else if($content instanceof static) {

			$this->headers = $content->header();
			$this->status($content->status());
			$content = $content->content();

		/* Need to move to App or something
		// Validator response
		} else if ($content instanceof Validator) {
		
			if($errors = $content->errors()) {
				$this->status(400);
				$this->header('X-Status-Reason', 'Validation Failed');
			}

			$this->content(array('validation' => $errors));
		*/

		} else if ($content instanceof \SimpleXMLElement) {
		
			$this->header('Content-Type', 'text/xml; charset=utf-8');
			$content = $content->asXML();
		
		}

		$this->content = $content;
		return $this;
	}


	/**
	 * Is this function really that useful?
	 */
	public function appendContent($content)
	{
		// Merge with existing JSON response?
		if(is_array($content) AND is_array($this->content)) {

			//$this->content += $content;
			$this->content = array_merge($this->content, $content);

		} else if(is_array($content) OR is_array($this->content)) {
			throw new \Exception(__CLASS__ .': Cannot append array content to string content');

		} else {
			$this->content .= $content;
		}

		return $this;
	}

	/**
	 * Set/Delete the HTTP header if given, else return current headers
	 * 
	 * @param string $type HTTP header type
	 * @param string $content header content/value
	 * @return mixed
	 */
	public function header($type = NULL, $content = NULL)
	{
		if($type !== NULL) {

			if($content !== NULL) {
				$this->headers[$type] = $content;
			} else {
				unset($this->headers[$type]);
			}
			
			return $this;
		}

		return $this->headers;
	}

	/**
	 * Issue a redirect to the user agent
	 *
	 * @param string $url
	 * @param array $params
	 */
	public function redirect($url, array $params = NULL)
	{
		$this->status(Response::REDIRECT);

		if($params) {
			$url .= '?' . str_replace('+', '%20', http_build_query($params, TRUE, '&'));
		}

		$this->header('Location', $url);
		return $this;
	}

	/**
	 * Send the status, response headers, and body - then end the script
	 *
	 * @param string $status
	 * @param array $headers
	 * @param string $body
	 */
	public function send()
	{
		$status = $this->status;

		if(is_int($status) AND isset(self::$codes[$status]))
		{
			//print $status . ' = ' . self::$codes[$status] . "\n";
			$status = self::$codes[$status];
		}

		$status = (getenv('SERVER_PROTOCOL') ?: 'HTTP/1.1') . ' ' . $status;

		if ( ! headers_sent() AND $this->mode != 'cli') {

			// @todo Remove all cookie headers to fix double session-cookie bug
			header('Set-Cookie: ', true);
			
			// Send status header
			header($status);

			// Send all stored headers
			foreach ($this->headers as $type => $value)
			{
				header($type . ': ' . $value, TRUE);
			}

		} else if ($this->mode == 'cli') {

			print $status . "\n";

			// Send all stored headers
			foreach ($this->headers as $type => $value)
			{
				print $type . ': ' . $value . "\n";
			}

			print "\n";
		}

		if(is_array($this->content))
		{
			$this->content = json_encode($this->content);
		}

		print $this->content;
	}
}