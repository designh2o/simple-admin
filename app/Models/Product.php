<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 17:02
 */

namespace App\Models;


class Product extends Model
{
	protected $tableName = 'products';

	protected $fields = [
		'id', 'name', 'is_active', 'section_id'
	];

	protected $casts = [
		'is_active' => 'boolean',
	];

	protected $belongsTo = [
		'section' => [
			'model' => Section::class,
			'condition' => 'products.section_id = sections.id',
		]
	];

	protected $hasMany = [
		'reviews' => [
			'model' => Review::class,
			'foreign_key' => 'product_id',
		]
	];
}