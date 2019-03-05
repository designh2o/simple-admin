<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 14:46
 */

namespace App;


/**
 * Class Router
 * @package App
 */
class Router
{
	private static $instance;
	private $request;
	private $supportedHttpMethods = array(
		"GET",
		"POST",
		"PUT",
		"DELETE",
	);

	public function __construct()
	{
		$this->request = Request::getInstance();
	}

	public static function getInstance()
	{
		if (null === self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * handle dynamic method calls into the model
	 * @param $name
	 * @param $args
	 */
	public function __call($name, $args)
	{
		list($route, $callback) = $args;
		if (!in_array(strtoupper($name), $this->supportedHttpMethods)) {
			$this->invalidMethodHandler();
		}
		$this->{strtolower($name)}[$this->formatRoute($route)] = $this->parseCallback($callback);
	}

	/**
	 * Removes trailing forward slashes from the right of the route.
	 * @param $route (string)
	 * @return string
	 */
	private function formatRoute($route)
	{
		$result = rtrim($route, '/');
		if ($result === '') {
			return '/';
		}
		return $result;
	}

	private function invalidMethodHandler()
	{
		header("{$this->request->getServerProtocol()} 405 Method Not Allowed");
	}

	private function defaultRequestHandler()
	{
		header("{$this->request->getServerProtocol()} 404 Not Found");
	}

	/**
	 * Method searches for a suitable router and return his callback
	 * @param $methodDictionary
	 * @param $formatedRoute
	 * @return array|null
	 */
	protected function findCallback($methodDictionary, $formatedRoute)
	{
		if(isset($methodDictionary[$formatedRoute])) {
			return $methodDictionary[$formatedRoute];
		}
		foreach ($methodDictionary as $route => $callback){
			if (preg_match('#' . $route . '#i', $formatedRoute, $matches)) {
				if(count($matches) == 2){
					$callback[] = $matches[1];
					return $callback;
				}
			}
		}
		return null;
	}

	/**
	 * Resolves a route
	 */
	protected function resolve()
	{
		$methodDictionary = $this->{strtolower($this->request->getRequestMethod())};
		$formatedRoute = $this->formatRoute($this->request->getRequestUri());
		$callback = $this->findCallback($methodDictionary, $formatedRoute);
		if (is_null($callback)) {
			$this->defaultRequestHandler();
			return;
		}
		$class = '\\App\\Controllers\\'.$callback[0];
		$method = $callback[1];
		$paramsForMethod = null;
		if(isset($callback[2])){
			$paramsForMethod = $callback[2];
		}
		$controller = new $class($this->request);
		$controller->$method($paramsForMethod);
	}

	public function __destruct()
	{
		$this->resolve();
	}

	private function parseCallback($callback, $default = null)
	{
		return mb_strpos($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
	}
}