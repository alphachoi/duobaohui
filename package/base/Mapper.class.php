<?php
namespace Snake\Package\Base;
#use \Snake\Package\Twitter\Helper\DBTwitterHelper;

abstract class Mapper{
	//需要获取的列
	protected $col = "*";
	//键值
	protected $key = "";
	//是否从主库读取
	protected $master = FALSE;
	

	public function selectStmt() {
		
	}

    public function __construct(array $enforce) {
	}

	/*function createCollection($array){
		$collection = $this->doCreateCollection($array);	
		return $collection;
	}*/
	function col($col) {
		$this->col = $col;
	}
	/*function key($key) {
		$this->key = $key;
	}*/
	function master($master) {
		$this->master = $master;
	}
	function insert($sql, $sqlData) {
		return $this->doInsert($sql, $sqlData);			
	}
	function update($sql, $sqlData) {
		return $this->doUpdate($sql, $sqlData);			
	}
	function get($sql, $sqlData) {
		return $this->doGet($sql, $sqlData);	
	}
	//生成多个推对象的集合
	//protected abstract function doCreateCollection(array $array);
	//插入到数据库的操作
	abstract function doInsert($sql, array $sqlData);
	//更新到数据库的操作
	//abstract function update();
	//abstract function doUpdate($sql, array $sqlData);			
	//获取数据接口
	abstract function doGet($sql, array $sqlData);			
	
}
