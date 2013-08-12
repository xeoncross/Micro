<?php

namespace Micro;

/**
 * Provide basic wrapper around simple login services
 */
class Login
{
	/**
	 * Verify a BrowserID assertion and return the user object
	 *
	 * @param string $assertion
	 * @param string $host
	 * @return array|null
	 */
	public function browserID($assertion, $host = NULL)
	{
		/*
		curl_setopt_array($h = curl_init('https://verifier.login.persona.org/verify'),array(
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_POST=>1,
			CURLOPT_POSTFIELDS=>"assertion=$assertion&audience=" . ($host ?: 'http://'. getenv('HTTP_HOST'))
		));

		return json_decode(curl_exec($h));
		*/

		$c = stream_context_create(array('http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded',
			'content'=> "assertion=$assertion&audience=" . ($host ?: 'http://'. getenv('HTTP_HOST')),
			//'ignore_errors' => true
		)));

		$data = file_get_contents('https://verifier.login.persona.org/verify', 0, $c);

		if($data AND ($data = json_decode($data, true)))
		{
			return $data;
		}
	}


	/**
	 * Verify and require a valid HTTP Digest Auth login
	 *
	 * @param array $users in array(user => password) form
	 * @param string $realm shown in auth box
	 * @param boolean $exit
	 * @return boolean
	 */
	public function hmac_http_auth(array $users, $realm = "Secured Area", $exit = TRUE)
	{
		if( ! empty($_SERVER['PHP_AUTH_DIGEST']))
		{
			// Decode the data the client gave us
			$default = array('nounce', 'nc', 'cnounce', 'qop', 'username', 'uri', 'response');
			preg_match_all('~(\w+)="?([^",]+)"?~', $_SERVER['PHP_AUTH_DIGEST'], $matches);
			$data = array_combine($matches[1] + $default, $matches[2]);

			// Generate the valid response
			$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
			$A2 = md5(getenv('REQUEST_METHOD').':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

			// Compare with what was sent
			if($data['response'] === $valid_response)
			{
				return TRUE;
			}
		}

		if( ! $exit) return FALSE;

		// Failed, or haven't been prompted yet
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Digest realm="' . $realm.
			'",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');
		exit();
	}
}