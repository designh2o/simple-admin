<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 19:17
 */

namespace App\Models;


class Section extends Model
{
	protected $tableName = 'sections';

	protected $fields = [
		'id', 'name', 'description'
	];
}