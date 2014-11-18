<?php
/**
 * CURL
 *
 * Provides a wrapper around cURL
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

namespace Micro;

class CURL
{
	public $ch;

	public function __construct()
	{
		$this->ch = curl_init();
	}

	/**
	 * Make a request to the given URL using cURL.
	 *
	 * @param string $url to request
	 * @param array $options for cURL object
	 * @return object
	 */
	public function request($url, array $options = NULL)
	{
		// Connection options override defaults if given
		$options = (array) $options + array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 1,
			CURLOPT_HTTPHEADER => array('Expect:'), // Disable the 100-continue header
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 10,			// No cURL request can last more than 10 seconds
			CURLOPT_CONNECTTIMEOUT => 10,	// No connection response can take more than 10 seconds
			//CURLOPT_FAILONERROR => true,	// Silently fail if the HTTP code is >= 400
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false
		);

		curl_setopt_array($this->ch, $options);

		$response = curl_exec($this->ch);
		$header = '';

		if($options[CURLOPT_HEADER])
		{
			list($header, $response) = explode("\r\n\r\n", $response, 2) + array('', '');

			if($header)
			{
				$headers = array();
				foreach(explode("\n", $header) as $line)
				{
					if(strpos($line, ':'))
					{
						list($key, $value) = explode(':', $line, 2);
						$headers[$key] = trim($value);
					}
				}
				$header = $headers;
			}
		}

		// Create a response object
		$object = new \stdClass;
		$object->body = $response;
		$object->header = $header;

		// Get additional request info
		$object->error_code = curl_errno($this->ch);
		$object->error = curl_error($this->ch);
		$object->info = curl_getinfo($this->ch);

		if($object->error)
		{
			error_log($object->error_code . ': '. $object->error);
		}

		return $object;
	}

	public function __destruct()
	{
		curl_close($this->ch);
	}
}
