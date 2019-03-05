<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 15:10
 */

namespace App\Controllers;


class AdminController extends Controller
{
	public function index()
	{
		$this->render('pages/index.html.twig');
	}

}