<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 14:44
 */

namespace App;


use App\Contracts\RequestInterface;

/**
 * Class Request for handle requests
 * @package App
 */
class Request implements RequestInterface
{
	private static $instance;
	private $requestMethod;
	private $requestUri;
	private $serverProtocol;
	private $body;

	function __construct()
	{
		$this->bootstrapSelf();
		$this->getBody();
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
	 * get fields from $_SERVER to properties into model
	 */
	private function bootstrapSelf()
	{
		foreach ($_SERVER as $key => $value) {
			$this->{$this->toCamelCase($key)} = $value;
		}
	}

	/**
	 * get strint to camelCase format
	 * @param $string
	 * @return mixed|string
	 */
	private function toCamelCase($string)
	{
		$result = strtolower($string);

		preg_match_all('/_[a-z]/', $result, $matches);
		foreach ($matches[0] as $match) {
			$c = str_replace('_', '', strtoupper($match));
			$result = str_replace($match, $c, $result);
		}
		return $result;
	}

	/**
	 * get request body
	 * @return array
	 */
	public function getBody()
	{
		if(isset($this->body)){
			return $this->body;
		}
		if($this->requestMethod == "DELETE" || $this->requestMethod == "PUT"){
			parse_str(file_get_contents('php://input'), $request);
			$body = array();
			foreach ($request as $key => $value) {
				$body[$key] = $value;	//todo: filter input
			}
			$this->body = $body;
		}
		if ($this->requestMethod == "POST") {
			$body = array();
			foreach ($_POST as $key => $value) {
				$body[$key] = $value;
			}
			$this->body = $body;
		}
		if ($this->requestMethod == "GET") {
			$body = array();
			foreach ($_GET as $key => $value) {
				$body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
			}
			$this->body = $body;
		}
		return $this->body;
	}

	public function get($name)
	{
		if(isset($this->body[$name])){
			return $this->body[$name];
		}
		return null;
	}

	/**
	 * handle dynamic properties into model
	 * @param $name
	 * @return |null
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	public function getRequestMethod()
	{
		return $this->requestMethod;
	}

	public function getRequestUri()
	{
		if(mb_strpos($this->requestUri, '?') !== false){
			$this->requestUri = explode('?', $this->requestUri)[0];
		}
		return $this->requestUri;
	}

	public function getServerProtocol()
	{
		return $this->serverProtocol;
	}
}