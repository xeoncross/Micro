<?php

namespace Micro;

class Requests
{
	public $handle;

	public function __construct()
	{
		$this->handle = curl_multi_init();
	}

	public function process($urls, $callback, array $options = NULL)
	{
		foreach($urls as $url)
		{
			$ch = curl_init();

			// Connection options override defaults if given
			$settings = (array) $options + array(
				CURLOPT_URL => $url,
				//CURLOPT_HEADER => 1,
				CURLOPT_HTTPHEADER => array('Expect:'), // Disable the 100-continue header
				CURLOPT_FOLLOWLOCATION => TRUE, // Follow 301, 302, and 307 redirects to new locations
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT => 11,			// No cURL request can last more than 10 seconds
				CURLOPT_CONNECTTIMEOUT => 10,	// No connection response can take more than 10 seconds
				//CURLOPT_FAILONERROR => true,	// Silently fail if the HTTP code is >= 400
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false
			);

			curl_setopt_array($ch, $settings);

			//curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => TRUE));
			//curl_setopt($ch, CURLOPT_MAXCONNECTS, 2);

			curl_multi_add_handle($this->handle, $ch);
		}

		do {
			$mrc = curl_multi_exec($this->handle, $active);

			if ($state = curl_multi_info_read($this->handle))
			{
				//print_r($state);
				$info = curl_getinfo($state['handle']);
				//print_r($info);
				$callback(curl_multi_getcontent($state['handle']), $info);
				curl_multi_remove_handle($this->handle, $state['handle']);
			}

			usleep(10000);

		} while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

	}

	public function __destruct()
	{
		curl_multi_close($this->handle);
	}
}
