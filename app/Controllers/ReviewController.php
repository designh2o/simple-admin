<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 05.03.19
 * Time: 3:36
 */

namespace App\Controllers;


use App\Models\Review;

class ReviewController extends Controller
{
	public function delete()
	{
		$productId = $this->request->id;
		try {
			$product = Review::find($productId);
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

	public function massUpdate()
	{
		$reviews = $this->request->review;
		$productId = $this->request->product_id;
		$formatReviews = [];
		foreach ($reviews['id'] as $key => $id){
			if($reviews['author'][$key] == "" || $reviews['date'][$key] == "" || $reviews['text'][$key] == ""){
				continue;
			}
			$formatReviews[] = [
				'id' => $id,
				'author' => $reviews['author'][$key],
				'date' => $reviews['date'][$key],
				'text' => $reviews['text'][$key],
				'product_id' => $productId,
			];
		}
		try {
			foreach ($formatReviews as $review) {
				if ($review['id'] <= 0) {    //create review
					Review::create($review);
				} else {
					Review::update($review, $review['id']);
				}
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