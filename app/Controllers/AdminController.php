<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 15:10
 */

namespace App\Controllers;


use App\Application;
use App\Contracts\RequestInterface;
use App\Models\Product;
use App\Models\Section;

class AdminController extends Controller
{
	public function index()
	{
		$products = Product::where('is_active', true)->where('id', 1);
		$sections = Section::get();
		$total = $products->getTotalCount();
		$products = $products
			->limit($this->getLimit())
			->offset($this->getOffset())
			->get();
		$this->render('pages/index.html.twig', [
			'total' => $total,
			'products' => $products,
			'sections' => $sections,
		]);
	}

}