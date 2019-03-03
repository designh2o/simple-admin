<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 14:44
 */

namespace App\Contracts;


interface RequestInterface
{
	public function getRequestMethod();

	public function getRequestUri();

	public function getServerProtocol();

	public function getBody();
}