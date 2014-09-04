<?php
/**
 * App
 *
 * The application instance which handles mapping routes to controllers
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2013 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Micro;

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;

class App extends Events
{
	protected $controllers = array();

	/**
	 * Return the controller response for the given request object
	 */
	public function run(Request $request)
	{
		$response = new Response();

		$path = trim($request->getPathInfo(), '/');
		$method = $request->getMethod();

		//var_dump($path, $method);

		try {

			foreach($this->controllers as $route => $controller) {

				// Skip index route for all paths
				if( ! $route AND $path) continue;

				$params = array();

				// Is this a regex? Regex must start with a tilde (~)
				if($route AND $route{0} === '~') {

					if( ! preg_match($route, $path, $matches)) {
						continue;
					}

					$complete = array_shift($matches);

					// The following code tries to solve:
					// /^path/(\w+)/ + "path/foo/bar" = array('foo', 'bar')

					// Skip the regex match and continue from there
					$params = explode('/', trim(mb_substr($path, mb_strlen($complete)), '/'));

					if($params[0]) {

						// Add captured group back into params
						foreach($matches as $match) {
							array_unshift($params, $match);
						}

					} else {
						$params = $matches;
					}

				} else if($route) {

					if(mb_substr($path, 0, mb_strlen($route)) !== $route) {
						continue;
					}

					$params = explode('/', trim(mb_substr($path, mb_strlen($route)), '/'));
				}

				$callback = $controller['callback'];

				if( ! is_object($callback) AND is_string($callback)) {
					$callback = new $callback($request);
				}

				if($controller['methods']) {
					// Does this closure support this HTTP method?
					if ( ! in_array($method, $controller['methods'])) {
						
						$response = new Response();
						$response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);

						// 405 requires the response to contain a list of "Allow[ed]" methods
						$response->headers->set('Allow', strtoupper(join(', ', $controller['methods'])));
						$this->emit('method_not_allowed', $request, $response);
						
						return $response;
					}
				}
			
				array_unshift($params, $request);
				$result = call_user_func_array($callback, $params);
				
				if($result instanceof Response) {
					return $result;
				
				} else if(is_int($result)) {
					$response->setStatusCode($result);

				} else if(is_array($result)) {

					$response->setContent(json_encode($result));
					$response->headers->set('Content-Type', 'application/json');

				} else if($response === false) {
					$response->setStatusCode(Response::HTTP_UNAUTHORIZED);

				} else if($response === null) {
					$response->setStatusCode(Response::HTTP_NOT_FOUND);

				} else if($response) {
					$response->setContent('' . $result);
				}

				return $response;
			}

			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$this->emit('not_found', $request, $response);

		} catch(\Exception $exception) {
			
			$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			
			if($this->emit('exception', $exception, $request, $response)) {
				throw $exception;
			}

		}
		
		return $response;
	}

	/**
	 * Set the callback for the given path
	 *
	 * @param string $path
	 * @param closure $callback
	 * @param boolean $overwrite
	 */
	public function map($route, $callback, $methods = null, $overwrite = true)
	{
		$route = trim($route, '/');

		if( ! is_array($methods)) {
			$methods = explode('|', $methods);
		}

		$methods = array_filter($methods);

		/* Removed for PHP 5.3 support
		if($callback instanceof \Closure) {
			if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
				$callback->bindTo($this);
			}
		}
		*/
		
		if(empty($this->controllers[$route]) OR $overwrite) {

			$this->controllers[$route] = array(
				'callback' => $callback,
				'methods' => $methods
			);
		}

		return $this;
	}
}