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
	private $app;
	private $twig;
	protected $request;

	public function __construct(RequestInterface $request)
	{
		$this->app = Application::getInstance();
		$this->twig = $this->app->getTwig();
		$this->request = $request;
	}

	/**
	 * Render twig template
	 * @param $template
	 * @param array $variables
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public function render($template, $variables = [])
	{
		print $this->twig->render($template, $variables);
	}

	/**
	 * output json
	 * @param $values
	 */
	public function json($values)
	{
		print json_encode($values);
	}
}
