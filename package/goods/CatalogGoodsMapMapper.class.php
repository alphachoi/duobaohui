<?php
namespace Snake\Package\Goods;

Use \Snake\Package\Goods\Helper\DBCatalogHelper;
use \Snake\libs\Cache\Memcache;

class CatalogGoodsMapMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('catalog_id');	
	private $catalogGoodsMapInfo =  array();

    public function __construct($catalogGoodsMapInfo = array()) {
		//parent::__construct($this->enforce);
		$this->catalogGoodsMapInfo = $catalogGoodsMapInfo;
	}

    public function getCatalogGoodsMapInfo() {
        return $this->catalogGoodsMapInfo;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	//TODO
	public function doInsert($sql,array $sqlData) {
	}

	public function doUpdate() {
	}

    public function doGet($sql, array $sqlData) {
		$this->catalogGoodsMapInfo = DBCatalogHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->catalogGoodsMapInfo;
    }
}
