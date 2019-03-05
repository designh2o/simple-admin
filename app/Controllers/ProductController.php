<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 20:55
 */

namespace App\Controllers;


use App\Models\Product;
use App\Pagination;

class ProductController extends Controller
{
	public function store()
	{
		$values = $this->request->getBody();
		if (!isset($values['is_active'])) {
			$values['is_active'] = false;
		}
		try {
			$product = Product::create($values);
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
		}
		$this->json([
			'success' => true,
			'id' => $product->id,
		]);
	}

	public function show($id)
	{
		$product = Product::find($id);
		$this->json($product->toArray());
	}

	public function index()
	{
		/** @var Pagination $products */
		$products = Product::paginate();
		$this->json([
			'items' => $products->toArray(),
			'currentPage' => $products->getCurrentPage(),
			'countPage' => $products->getCountPage(),
		]);
	}

	public function update()
	{
		try {
			$productId = $this->request->id;
			$values = $this->request->getBody();
			if (!isset($values['is_active'])) {
				$values['is_active'] = false;
			}
			$product = Product::find($productId);
			if (!$product) {
				throw new \Exception('invalid id ' . $productId);
			}
			$product->update($values);
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}

	public function delete()
	{
		try {
			$productId = $this->request->id;
			$product = Product::find($productId);
			if (!$product) {
				$this->json([
					'error' => true,
					'message' => 'invalid id ' . $productId
				]);
				return;
			}
			$product->delete();
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}

	public function massDelete()
	{
		$ids = $this->request->ids;
		try {
			foreach ($ids as $id) {
				$product = Product::find($id);
				if (!$product) {
					throw new \Exception('invalid id ' . $id);
				}
				$product->delete();
			}
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}

	public function massUpdate()
	{
		$ids = $this->request->ids;
		$body = $this->request->getBody();
		$formatProducts = [];
		foreach ($ids as $key => $id){
			if($body['name'][$key] == ""){
				continue;
			}
			$formatProducts[] = [
				'id' => $id,
				'name' => $body['name'][$key],
				'is_active' => $body['is_active'][$key],
				'section_id' => $body['section_id'][$key],
			];
		}
		try {
			foreach ($formatProducts as $formatProduct) {
				$product = Product::find($formatProduct['id']);
				if (!$product) {
					throw new \Exception('invalid id ' . $formatProduct['id']);
				}
				$product->update($formatProduct);
			}
		}catch (\Exception $exception){
			$this->json([
				'error' => true,
				'message' => $exception->getMessage()
			]);
			return;
		}
		$this->json([
			'success' => true
		]);
	}
}