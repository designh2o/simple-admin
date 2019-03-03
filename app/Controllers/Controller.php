<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 16:24
 */

namespace App\Controllers;


use App\Application;
use App\Contracts\RequestInterface;

abstract class Controller
{
	const PAGE_LIMIT = 10;
	private $app;
	private $twig;
	protected $request;

	public function __construct(RequestInterface $request)
	{
		$this->app = Application::getInstance();
		$this->twig = $this->app->getTwig();
		$this->request = $request;
	}

	public function render($template, $variables = [])
	{
		print $this->twig->render($template, $variables);
	}

	public function json($values)
	{
		print json_encode($values);
	}

	/**
	 * @return int
	 */
	public function getCurrentPage()
	{
		$page = (int)$this->request->page;
		if($page > 0){
			return $page;
		}
		return 1;
	}

	public function getLimit()
	{
		$limit = (int)$this->request->count;
		if($limit > 0){
			return $limit;
		}
		return static::PAGE_LIMIT;
	}

	public function getOffset()
	{
		$page = $this->getCurrentPage();
		$limit = $this->getLimit();
		$offset = 0;
		if ($page != 0 && $page != 1) {
			$offset = ($page - 1) * $limit;
		}
		return $offset;
	}
}