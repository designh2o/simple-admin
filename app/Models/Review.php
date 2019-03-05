<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 04.03.19
 * Time: 21:49
 */

namespace App\Models;


class Review extends Model
{
	protected $tableName = 'reviews';

	protected $fields = [
		'id', 'author', 'text', 'date', 'product_id'
	];

	protected $casts = [
		'date' => 'date'
	];

}