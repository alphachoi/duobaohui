<?php
namespace Snake\Package\Goods;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Goods\Helper\DBGoodsHelper;
use \Snake\libs\Cache\Memcache AS Memcache;

class GoodsMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('goods_id');	
	private $goods = array();

	public function __construct($goods = array()) {
		parent::__construct($this->enforce);
		$this->goods = $goods;
	}

	public function getGoods() {
		return $this->goods;
	}   

	public function router($method, $prefix, $extra = array()) {
		$goto = $method . '__' . $prefix;
		return call_user_func(array($this, $goto), $extra);
	}
	//TODO
	public function doInsert($sql,array $sqlData) {
	}
	//TODO
	public function doUpdate() {
	}

	/* 批量获取twitter内容 */
	public function doGet($sql, array $sqlData) {
		$this->goods = DBGoodsHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->goods;
	}

	public function storageGet($table, $col, $sql) {
		$this->goods = MlsStorageService::GetQueryData($table, NULL, $col, $sql);
		//错误处理 
		if (!is_array($this->goods) || empty($this->goods)) {
			$this->goods = array();
		}
		return $this->goods;
	}

	public function storageMultiRowGet($table, $keyName, $keyVal, $filter, $forceIndex, $start, $limit, $orderBy, $orderDir, $columnNames, $hashKey = '') {
		$this->goods = MlsStorageService::MultiRowGetUniq($table, $keyName, $keyVal, $filter, $forceIndex, $start, $limit, $orderBy, $orderDir, $columnNames, $hashKey);
		//错误处理 
		if (!is_array($this->goods) || empty($this->goods)) {
			$this->goods = array();
		}
		return $this->goods;
	}

	public function storageUniqRowGet($table, $keyName, $keyVals, $filter, $columns, $hashKey = "") {
		$this->goods = MlsStorageService::UniqRowGetMulti($table, $keyName, $keyVals, $filter, $columns, $hashKey);
		//错误处理 
		if (!is_array($this->goods) || empty($this->goods)) {
			$this->goods = array();
		}
		return $this->goods;  
	}


}
