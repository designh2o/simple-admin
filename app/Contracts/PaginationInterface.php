<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 05.03.19
 * Time: 19:13
 */

namespace App\Contracts;


interface PaginationInterface
{
	public function setItems($items);

	public function getCurrentPage();

	public function getCountPage();

	public function getLimit();

	public function getOffset();
}