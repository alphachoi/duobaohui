<?php
namespace Snake\Package\Base;

abstract class Collection implements \Iterator{
	//数据库映射对象
	protected $object;

	protected $total = 0;
	//存储原始数据的数组
	protected $raw = array();

	private $result = array();
	//当前指针
	private $pointer = 0;
	//存储对象的数组
	private $objects = array();

    public function __construct(array $raw = NULL, DomainObject $object = NULL) {
		if (!is_null($raw) && !is_null($object)) {
			$this->raw = $raw;
			$this->total = count($raw);
		}
		$this->object = $object;
	}

	function add(DomainObject $object) {
		$this->objects[$this->total] = $object;
		$this->total ++;
	}

	function remove($key) {
		unset($this->objects[$key]); 
		unset($this->raw[$key]); 
		$this->raw = array_values($this->raw);
		$this->objects = array_values($this->objects);
		$this->pointer --;
		return TRUE;
	}

	protected function getRow($num) {
		if ($num > $this->total || $num < 0) {
			return NULL;
		}
		if (isset($this->objects[$num])) {
			return $this->objects[$num];
		}
		if (isset($this->raw[$num])) {
			$this->objects[$num] = new $this->object($this->raw[$num]);	
			return $this->objects[$num];	
		}
	}
	public function rewind() {
		$this->pointer = 0;
	}
	public function current() {
		return $this->getRow($this->pointer);
	}
	public function key() {
		return $this->pointer;	
	}
	public function next() {
		$row = $this->getRow($this->pointer);	
		if ($row) {
			$this->pointer ++;
		}
		return $row;
	}
	public function valid() {
		return (!is_null($this->current()));
	}
}
