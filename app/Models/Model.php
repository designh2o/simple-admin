<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 16:46
 */

namespace App\Models;

use App\QueryBuilder;

abstract class Model
{
	protected $tableName;
	protected $primaryKey = 'id';
	protected $fields = [];
	protected $relations = [];
	protected $data = [];

	public function __construct()
	{
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getRelations()
	{
		return $this->relations;
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

	public function formatRelations(&$data)
	{
		if(!empty($this->relations) && !empty($data)){
			foreach ($this->relations as $relationName => $relation){
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
		$this->formatRelations($data);
		$this->data = array_merge($this->data, $data);
	}

	public function __get($name)
	{
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		return null;
	}

	public function __isset($name)
	{
		if(in_array($name, $this->fields)){
			return true;
		}
		if(array_key_exists($name, $this->relations)){
			return true;
		}
		return false;
	}

	public function newQuery()
	{
		return new QueryBuilder($this);
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
		return $this->data;
	}
}