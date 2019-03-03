<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 20:55
 */

namespace App\Controllers;


use App\Models\Product;

class ProductController extends Controller
{
	public function store()
	{
		$product = Product::create($this->request->getBody());
		print '<pre>';
		print_r($product->toArray());
		print '</pre>';
	}

	public function show()
	{
		$productId = $this->request->id;
		$product = Product::find($productId);
		$this->json($product->toArray());
	}

	public function update()
	{
		$productId = $this->request->id;
		$values = $this->request->getBody();
		if(!isset($values['is_active'])){
			$values['is_active'] = false;
		}
		$product = Product::find($productId);
		if($product){
			$product->update($values);
		}
	}

	public function delete()
	{
		$productId = $this->request->id;
		$product = Product::find($productId);
		$product->delete();
	}
}