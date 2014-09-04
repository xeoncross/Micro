<?php
/**
 * Object-based Controller
 *
 * Provides a REST-ful object instance for the App routing object
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Micro;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
	/*
	function get();
	function post();
	function put();
	function patch();
	function delete();
	function options();
	*/

	public function __construct(Request $request)
	{
		// noop
	}
	

	public function __invoke(Request $request, $params = null)
	{
		// Does __invoke take args or what?
		$params = func_get_args();

		// Get the request object
		$request = array_shift($params);

		$method = strtolower($request->getMethod());

		// Try the global index if not supported
		if( ! method_exists($this, $method)) {

			$response = new Response();
			$response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);

			$methods = array_intersect(
				array('get', 'post', 'put', 'patch', 'delete', 'options'),
				get_class_methods($this)
			);

			// 405 requires the response to contain a list of "Allow[ed]" methods
			$response->header->set('Allow', strtoupper(join(', ', $methods)));

			return $response;
		}

		// Get Result
		$result = call_user_func_array(array($this, $method), $params);

		// Render HTML only for initial (non-AJAX) GET requests which return arrays
		if($method == 'get' AND ! $request->isXmlHttpRequest() AND is_array($result)) {
			$result = $this->html(str_replace('\\', '/', get_class($this)), $result);
		}

		//var_dump($result);

		return $result;
	}

	function html($view, $data)
	{
		$view = new View($view);
		$view->set($data);
		//$view->ajax($isAjax); // Will disable extends()

		return $view;
	}

}
