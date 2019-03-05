<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 03.03.19
 * Time: 17:27
 */

namespace App;


use App\Models\Model;

/**
 * Class QueryBuilder for build and execute query to datebase
 * @package App
 */
class QueryBuilder
{
	private $queryBuilder;
	private $model;
	private $doctrine;
	private $firstWhere = true;

	public function __construct(Model $model)
	{
		$application = Application::getInstance();
		$this->doctrine = $application->getDoctrine();
		$this->queryBuilder = $this->doctrine->createQueryBuilder();
		$this->model = $model;
		$this->queryBuilder
			->select($model->getTableName().'.*')	//start select
			->from($model->getTableName())
			->orderBy($model->getTableName().'.'.$model->getPrimaryKey(), 'desc');	//start order
		$belongsTo = $model->getBelongsTo();
		if(!empty($belongsTo)){
			//get belongs to model relations
			foreach ($belongsTo as $relationName => $relation){
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

	/**
	 * get one model by primary key
	 * @param $id
	 * @return Model|bool
	 */
	public function find($id)
	{
		$this->queryBuilder
			->where($this->model->getTableName().'.'.$this->model->getPrimaryKey().' = :id')
			->setParameter('id', $id);
		$result = $this->queryBuilder->execute();
		$data = $result->fetch();
		if($data){
			$this->model->setData($data);
			return $this->model;
		}
		return false;
	}

	public function query()
	{
		return $this;
	}

	/**
	 * @param mixed ...$args, number 2 or 3
	 * @return $this
	 */
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
		$where = $this->model->getTableName().".$field $operator :$field";
		if($this->firstWhere){
			$this->firstWhere = false;
			$this->queryBuilder->where($where);
		}else{
			$this->queryBuilder->andWhere($where);
		}
		$this->queryBuilder->setParameter($field, $value);

		return $this;
	}

	/**
	 * execute query and get array with models
	 * @return array
	 */
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

	/**
	 * execute query with limit and offset and get pagination object
	 * @param int $limit
	 * @return Pagination
	 */
	public function paginate($limit = 0)
	{
		$total = $this->getTotalCount();
		$pagination = new Pagination($total, $limit, $this->model->getTableName().'_');
		$this->queryBuilder
			->setMaxResults($pagination->getLimit())
			->setFirstResult($pagination->getOffset());
		$items = $this->get();
		$pagination->setItems($items);

		return $pagination;
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

	/**
	 * execute query for get records total count
	 * @return mixed
	 */
	public function getTotalCount()
	{
		$newQueryBuilder = clone $this->queryBuilder;
		$result = $newQueryBuilder->select('COUNT(*) as total')->execute()->fetch();
		return $result['total'];
	}

	public function create($values)
	{
		$values = $this->model->filterValues($values);
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
		$values = $this->model->filterValues($values);
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