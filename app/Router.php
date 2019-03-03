<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 14:46
 */

namespace App;


use App\Contracts\RequestInterface;

class Router
{
	private $request;
	private $supportedHttpMethods = array(
		"GET",
		"POST",
		"PUT",
		"DELETE",
	);

	public function __construct(RequestInterface $request)
	{
		$this->request = $request;
	}

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
	 * Resolves a route
	 */
	protected function resolve()
	{
		$methodDictionary = $this->{strtolower($this->request->getRequestMethod())};
		$formatedRoute = $this->formatRoute($this->request->getRequestUri());
		$callback = $methodDictionary[$formatedRoute];
		if (is_null($callback)) {
			$this->defaultRequestHandler();
			return;
		}
		$class = '\\App\\Controllers\\'.$callback[0];
		$method = $callback[1];
		$controller = new $class($this->request);
		$controller->$method();
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