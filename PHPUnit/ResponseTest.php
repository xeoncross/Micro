<?php
class ResponseTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		//\Micro\Response::$mode = 'cgi';
		//\Micro\Response::$mode = 'cli';
		//\Micro\Response::$mode = 'fastcgi';
	}

	public function testSend()
	{
		$headers = array(
			'Accept-Ranges' => 'bytes',
			'Content-Type' => 'text/html; charset=utf-8'
		);

		$response = new \Micro\Response('body');
		$response->header('Accept-Ranges', 'bytes');
		$response->header('Content-Type', 'text/html; charset=utf-8');


		ob_start();
		$response->send();
		ob_end_clean();

		$headers = xdebug_get_headers();

		$this->assertContains('Accept-Ranges: bytes', $headers);
		$this->assertContains('Content-Type: text/html; charset=utf-8', $headers);
	}

	/*
	public function testRedirect()
	{
		// Absolute
		$url = 'http://example.com/path/to/folder/';
		$params = array('foo' => 'bar', 'baz' => '');

		ob_start();
		\Kit\Response::redirect($url, $params);
		ob_end_clean();

		$headers = xdebug_get_headers();

		$location = 'Location: ' . $url . '?' . http_build_query($params);
		$this->assertContains($location, $headers);

		// Relative
		$url = 'path/to/folder';
		$params = array('foo' => 'bar', 'baz' => '');

		ob_start();
		\Kit\Response::redirect($url, $params);
		ob_end_clean();

		$headers = xdebug_get_headers();

		$location = 'Location: ' . KIT\URL_HOST . '/'. $url . '?' . http_build_query($params);
		$this->assertContains($location, $headers);
	}
	*/
}

