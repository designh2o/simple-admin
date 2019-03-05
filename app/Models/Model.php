<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 16:46
 */

namespace App\Models;

use App\Pagination;
use App\QueryBuilder;

abstract class Model
{
	protected $tableName;
	protected $primaryKey = 'id';
	protected $fields = [];
	protected $belongsTo = [];
	protected $hasMany = [];
	protected $data = [];
	protected $casts = [];

	public function __construct()
	{
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getBelongsTo()
	{
		return $this->belongsTo;
	}

	public function getHasMany()
	{
		return $this->hasMany;
	}

	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	public function getPrimary()
	{
		return $this->data[$this->primaryKey];
	}

	public function getData()
	{
		return $this->data;
	}

	public function load($relation)
	{
		if(!isset($this->hasMany[$relation])){
			throw new \Exception("Undefined model $relation!");
		}
		$relationClass = $this->hasMany[$relation]['model'];
		$this->$relation = $relationClass::where($this->hasMany[$relation]['foreign_key'], $this->getPrimary())->get();
	}

	public function formatBelongsTo(&$data)
	{
		if(!empty($this->belongsTo) && !empty($data)){
			foreach ($this->belongsTo as $relationName => $relation){
				${$relationName} = [];
				$relationModel = new $relation['model'];
				$relationTable = $relationModel->getTableName();
				foreach($data as $key => $value){
					if(strpos($key, $relationTable) === 0){
						${$relationName}[str_replace($relationTable.'_', '', $key)] = $value;
						unset($data[$key]);
					}
				}
				if(isset(${$relationName}[$relationModel->getPrimaryKey()])) {
					$relationModel->setData(${$relationName});
					$this->$relationName = $relationModel;
				}
			}
		}
	}

	public function getFullSelect()
	{
		$select = [];
		foreach ($this->fields as $field){
			$select[] = $this->tableName.'.'.$field.' as '.$this->tableName.'_'.$field;
		}
		return implode(', ', $select);
	}

	public function setData($data)
	{
		$this->formatBelongsTo($data);
		$this->data = array_merge($this->data, $data);
	}

	/**
	 * Handle dynamic field call into the model
	 * @param $name
	 * @return mixed|string|null
	 * @throws \Exception
	 */
	public function __get($name)
	{
		if(array_key_exists($name, $this->hasMany)){
			//get relations
			$this->load($name);
			return $this->$name;
		}
		if (isset($this->data[$name])) {
			return $this->getField($name);
		}
		return null;
	}

	/**
	 * get format field
	 * @param $name
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function getField($name)
	{
		if(isset($this->casts[$name])){
			switch ($this->casts[$name]){
				case 'boolean':
					return $this->getFormatBoolean($this->data[$name]);
					break;
				case 'date':
					return $this->getFormatDate($this->data[$name]);
					break;
			}
		}
		return $this->data[$name];
	}

	/**
	 * format boolean type field
	 * @param $value
	 * @return string
	 */
	protected function getFormatBoolean($value)
	{
		return $value ? 'yes' : 'no';
	}

	/**
	 * get format field date type
	 * @param $value
	 * @return string
	 * @throws \Exception
	 */
	protected function getFormatDate($value)
	{
		$date = new \DateTime($value);
		return $date->format('d.m.Y');
	}

	/**
	 * Handle dynamic field is set into the model
	 * @param $name
	 * @return bool
	 */
	public function __isset($name)
	{
		if(in_array($name, $this->fields)){
			return true;
		}
		if(array_key_exists($name, $this->belongsTo)){
			return true;
		}
		if(array_key_exists($name, $this->hasMany)){
			return true;
		}
		return false;
	}

	/**
	 * Create new query builder
	 * @return QueryBuilder
	 */
	public function newQuery()
	{
		return new QueryBuilder($this);
	}

	/**
	 * filter field that do not belong to the model and set empty field as null
	 * @param $values
	 * @return array
	 */
	public function filterValues($values)
	{
		foreach ($values as &$value){
			if($value === ""){
				$value = null;
			}
		}
		unset($value);
		$fields = $this->fields;
		$primaryKey = $this->primaryKey;
		return array_filter($values, function($key) use ($fields, $primaryKey){
			return in_array($key, $fields) && $key != $primaryKey;
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Handle dynamic method calls into the model.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return $this->newQuery()->$method(...$parameters);
	}

	/**
	 * Handle dynamic static method calls into the method.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		return (new static)->$method(...$parameters);
	}

	public function toArray()
	{
		$result = [];
		foreach($this->data as $key => $value){
			$result[$key] = $this->getField($key);
		}
		foreach($this->belongsTo as $name => $relation){
			if($this->$name) {
				$result[$name] = $this->$name->toArray();
			}
		}
		foreach ($this->hasMany as $name => $relation){
			$items = [];
			if(is_array($this->$name)) {
				foreach ($this->$name as $item) {
					$items[] = $item->toArray();
				}
			}
			$result[$name] = $items;
		}
		return $result;
	}
}