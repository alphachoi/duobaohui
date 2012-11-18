<?php
namespace Snake\Package\Goods;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Goods\Helper\DBGoodsVerifyHelper;
use \Snake\libs\Cache\Memcache AS Memcache;

class GoodsVerifyMapper extends \Snake\Package\Base\Mapper{
	private $goodsVerify = array();

	public function __construct($goodsVerify = array()) {
		$this->goodsVerify = $goodsVerify;
	}

	public function getGoodsVerify() {
		return $this->goodsVerify;
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
		$this->goodsVerify = DBGoodsVerifyHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->goodsVerify;
	}

}
