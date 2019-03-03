<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 14:44
 */

namespace App;


use App\Contracts\RequestInterface;

class Request implements RequestInterface
{

	private $requestMethod;
	private $requestUri;
	private $serverProtocol;
	private $body;

	function __construct()
	{
		$this->bootstrapSelf();
	}

	private function bootstrapSelf()
	{
		foreach ($_SERVER as $key => $value) {
			$this->{$this->toCamelCase($key)} = $value;
		}
	}

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
				$body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
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

	public function __get($name)
	{
		$body = $this->getBody();
		if(isset($body[$name])){
			return $body[$name];
		}
		return null;
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