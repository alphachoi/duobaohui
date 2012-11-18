<?php
namespace Snake\Package\Goods;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Goods\Helper\DBGoodsReportHelper;
use \Snake\libs\Cache\Memcache AS Memcache;

class GoodsReportMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('twitter_id');	
	private $goodsReport = array();

	public function __construct($goodsReport = array()) {
		parent::__construct($this->enforce);
		$this->goodsReport = $goodsReport;
	}

	public function getGoodsReport() {
		return $this->goodsReport;
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
		$this->goodsReport = DBGoodsReportHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->goodsReport;
	}


}
