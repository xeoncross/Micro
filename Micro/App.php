<?php
namespace Micro;

/**
 * The application instance which handles all controller mappings/route callbacks
 */
class App extends Events
{
	protected $controllers = array();
	protected $mappings = array();

	/**
	 * Define common system defaults
	 */
	public function setup()
	{
		// A stream should respond to a connection attempt within ten seconds
		ini_set('default_socket_timeout', 10);

		// iconv encoding default
		iconv_set_encoding("internal_encoding", "UTF-8");

		// Multibyte encoding
		mb_internal_encoding('UTF-8');

		// Don't show SimpleXML/DOM errors (most of the web is invalid)
		libxml_use_internal_errors(true);

		// Please sir - use GMT instead of UTC for poor little MySQL's sake!
		date_default_timezone_set('GMT');
	}

	/**
	 * Convert all input to valid, UTF-8 strings with no control characters
	 */
	public function filterInput()
	{
		$_GET = I18n::filter($_GET, false);
		$_POST = I18n::filter($_POST, false);
		$_COOKIE = I18n::filter($_COOKIE, false);
	}

	/**
	 * Return the controller response for the given request object
	 */
	public function run(Request $request)
	{
		$response = new Response();

		$path = $request->path;
		$method = $request->method;
		$format = $request->format;

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
					// (Regex) "/^path/(\w+)/" + (Path) "path/word/other" = (Params) array(word, other)

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

				// Make sure this HTTP method is legit
				if($controller['methods']) {

					if( ! in_array($method, $controller['methods'])) {

						$response->status(Response::METHOD_NOT_ALLOWED);

						// 405 requires the response to contain a list of "Allow[ed]" methods
						$response->header('Allow', join(', ', $controller['methods']));

						$this->emit('app.method_not_allowed', $response, $request, $path);

						return $response;
					}
				}

				// Make sure the requested format is allowed
				if($controller['formats']) {

					if( ! in_array($format, $controller['formats'])) {

						$response->status(Response::NOT_ACCEPTABLE);

						// 406 *SHOULD* include a list of available entity characteristics and location(s)
						$response->content($controller['formats']);

						$this->emit('app.not_acceptable', $response, $request, $path);

						return $response;
					}
				}
			
				array_unshift($params, $response);
				$result = call_user_func_array($controller['callback'], $params);
				
				if(is_int($result) AND isset($response::$codes[$result])) {
					$response->status($result);
				} else if($result !== NULL) {
					$response->content($result);
				}

				return $response;
			}

			$response->status(Response::NOT_FOUND);
			$this->emit('app.not_found', $response, $request, $path);

		} catch(\Exception $exception) {
			
			//die(__FILE__);
			$response->status(Response::SERVER_ERROR);
			$this->emit('app.exception', $exception, $response, $request, $path);

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
	public function map($route, $callback, $methods = NULL, $formats = NULL, $overwrite = true)
	{
		$route = trim($route, '/');

		if($methods) {
			$methods = (array) $methods;
		}

		if($formats) {
			$formats = (array) $formats;
		}

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
				'formats' => $formats,
				'methods' => $methods
			);
		}

		return $this;
	}
}