<?php
/**
 * Created by PhpStorm.
 * User: Andrey Dubinin
 * Date: 04.03.19
 * Time: 23:13
 */

namespace App;


use App\Contracts\PaginationInterface;

/**
 * Class Pagination
 * @package App
 */
class Pagination implements \Iterator, PaginationInterface
{
	/** @var int default limit page */
	const PAGE_LIMIT = 5;

	protected $request;
	public $total;
	public $limit;
	public $currentPage;
	public $countPage;
	public $name;
	protected $items;

	public function __construct($total, $limit = 0, $prefix = "")
	{
		$this->total = $total;
		$this->name = $prefix."page";
		if($limit <= 0){
			$limit = static::PAGE_LIMIT;
		}
		$this->limit = $limit;
		$this->request = Request::getInstance();
		$this->currentPage = $this->getCurrentPage();
		$this->countPage = $this->getCountPage();
	}

	public function current()
	{
		return current($this->items);
	}

	public function key()
	{
		return key($this->items);
	}

	public function next(): void
	{
		next($this->items);
	}

	public function rewind(): void
	{
		reset($this->items);
	}

	public function valid(): bool
	{
		return null !== key($this->items);
	}

	public function toArray()
	{
		$result = [];
		foreach ($this->items as $item){
			$result[] = $item->toArray();
		}
		return $result;
	}

	public function setItems($items)
	{
		$this->items = $items;
	}

	/**
	 * @return int
	 */
	public function getCurrentPage()
	{
		$page = (int)$this->request->get($this->name);
		if($page > 0){
			return $page;
		}
		return 1;
	}

	public function getCountPage()
	{
		return ceil($this->total / $this->limit);
	}

	public function getLimit()
	{
		$limit = (int)$this->request->count;
		if($limit < 0){
			return 999;	//lots to get all
		}
		if($limit > 0){
			return $limit;
		}
		return static::PAGE_LIMIT;
	}

	public function getOffset()
	{
		$page = $this->getCurrentPage();
		$limit = $this->getLimit();
		$offset = 0;
		if ($page != 0 && $page != 1) {
			$offset = ($page - 1) * $limit;
		}
		return $offset;
	}
}