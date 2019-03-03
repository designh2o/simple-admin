<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 17:27
 */

namespace App;


use App\Models\Model;

class QueryBuilder
{
	private $queryBuilder;
	private $model;
	private $doctrine;

	public function __construct(Model $model)
	{
		$application = Application::getInstance();
		$this->doctrine = $application->getDoctrine();
		$this->queryBuilder = $this->doctrine->createQueryBuilder();
		$this->model = $model;
		$this->queryBuilder->select($model->getTableName().'.*')->from($model->getTableName());
		$relations = $model->getRelations();
		if(!empty($relations)){
			foreach ($relations as $relationName => $relation){
				$relationModel = new $relation['model'];
				$relationTable = $relationModel->getTableName();
				$this->queryBuilder->addSelect($relationModel->getFullSelect());
				$this->queryBuilder
					->leftJoin(
						$model->getTableName(),
						$relationTable,
						$relationTable,
						$relation['condition']
					);
			}
		}
	}

	public function select($select)
	{
		if(is_array($select)){
			$select = implode(', ', $select);
		}
		$this->queryBuilder->select($select);
	}

	public function find($id)
	{
		$this->queryBuilder
			->where($this->model->getTableName().'.id = :id')
			->setParameter('id', $id);
		$result = $this->queryBuilder->execute();
		$data = $result->fetch();
		if($data){
			$this->model->setData($data);
			return $this->model;
		}
		return false;
	}

	public function where(...$args)
	{
		switch (func_num_args()){
			case 2:
				$field = $args[0];
				$operator = "=";
				$value = $args[1];
				break;
			case 3:
				$field = $args[0];
				$operator = $args[1];
				$value = $args[2];
				break;
			default:
				return $this;
		}
		$this->queryBuilder->where($this->model->getTableName().".$field $operator :$field")->setParameter($field, $value);
		return $this;
	}

	public function get()
	{
		$items = [];
		$result = $this->queryBuilder->execute();
		while($row = $result->fetch()){
			$model = clone $this->model;
			$model->setData($row);
			$items[] = $model;
		}
		return $items;
	}

	public function limit($limit)
	{
		$this->queryBuilder->setMaxResults($limit);
		return $this;
	}

	public function offset($offset)
	{
		$this->queryBuilder->setFirstResult($offset);
		return $this;
	}

	public function getTotalCount()
	{
		$newQueryBuilder = clone $this->queryBuilder;
		$result = $newQueryBuilder->select('COUNT(*) as total')->execute()->fetch();
		return $result['total'];
	}

	public function create($values)
	{
		$id = null;
		$this->doctrine->beginTransaction();
		try{
			$prepareValues = [];
			foreach ($values as $key => $value){
				$prepareValues[$key] = ":".$key;
			}
			$this->queryBuilder->insert($this->model->getTableName())
				->values($prepareValues)
				->setParameters($values)
				->execute();
			$id = $this->doctrine->lastInsertId();
			$values['id'] = $id;
			$this->model->setData($values);
			$this->doctrine->commit();
		}catch (\Exception $exception){
			$this->doctrine->rollBack();
			throw $exception;
		}
		return $this->model;
	}

	public function update($values, $id = null)
	{
		if(is_null($id)){
			$id = $this->model->getPrimary();
		}
		$id = (int)$id;
		if($id <= 0){
			throw new \Exception('invalid id');
		}
		$this->model->setData($values);
		$this->queryBuilder->update($this->model->getTableName())
			->where('id = :id')
			->setParameter('id', $id);
		foreach ($values as $key => $value){
			if($key == $this->model->getPrimaryKey()){
				continue;
			}
			$this->queryBuilder->set($key, ':'.$key)
				->setParameter($key, $value);
		}
		$this->queryBuilder->execute();

		return $this->model;

	}

	public function delete($id = null)
	{
		if(is_null($id)){
			$id = $this->model->getPrimary();
		}
		$id = (int)$id;
		if($id <= 0){
			throw new \Exception('invalid id');
		}
		$this->queryBuilder->delete($this->model->getTableName())
			->where('id = :id')
			->setParameter('id', $id)
			->execute();
	}

}