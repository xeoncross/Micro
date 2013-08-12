<?php

namespace Micro;

/**
 * Perform OAuth 2.0 authorization against service providers
 */
class OAuth2
{
	public $client_id, $client_secret, $auth_url, $token_url;

	/**
	 * Create a new OAuth2 instance
	 *
	 * @param array $config
	 * @param bool $debug
	 */
	public function __construct($config, $debug = false)
	{
		foreach($config as $key => $value)
		{
			$this->$key = $value;
		}

		$this->debug = $debug;
	}

	/**
	 * Request an access token for the given service
	 *
	 * @param string $redirect_uri
	 * @param string $code
	 * @param string $state
	 * @param string $scope
	 * @return array
	 */
	public function getToken($redirect_uri, $code, $state, $scope = '')
	{
		$params = array(
			'client_id' => $this->client_id,
			'redirect_uri' => $redirect_uri,
			'scope' => $scope,
			'state' => $state
		);

		if($code)
		{
			$params = http_build_query($params + array(
				'client_secret' => $this->client_secret,
				'grant_type' => 'authorization_code',
				'code' => $code,
			));

			$c = stream_context_create(array('http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded\r\nContent-Length: ' . strlen($params),
				'content' => $params,
				'ignore_errors' => $this->debug == TRUE
			)));

			if($result = file_get_contents($this->token_url . '?' . $params, 0, $c))
			{
				if($this->debug)
				{
					return join("\n", $http_response_header) . "\n\n" . $result;
				}

				if($json = json_decode($result))
				{
					return $json;
				}

				parse_str($result, $pieces);
				return $pieces;
			}
		}
		else
		{
			$params['response_type'] = 'code';
			header("Location: " . $this->auth_url . '?' . http_build_query($params), TRUE, 307);
			die();
		}
	}
}