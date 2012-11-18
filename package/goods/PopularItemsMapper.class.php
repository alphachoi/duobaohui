<?php
namespace Snake\Package\Goods;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Goods\Helper\DBPopularItems;
use \Snake\libs\Cache\Memcache AS Memcache;

class PopularItemsMapper extends \Snake\Package\Base\Mapper{
	private $popularItems = array();

	public function __construct($popularItems = array()) {
		$this->popularItems = $popularItems;
	}

	public function getPopularItems() {
		return $this->popularItems;
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
		$this->popularItems = DBPopularItems::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->popularItems;
	}

}
