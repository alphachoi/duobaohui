<?php
namespace Snake\Package\Goods;

Use \Snake\Package\Goods\Helper\DBCatalogHelper;
use \Snake\libs\Cache\Memcache;

class CatalogMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('catalog_id');	
	private $catalogInfo =  array();

    public function __construct($catalogInfo = array()) {
		parent::__construct($this->enforce);
		$this->catalogInfo = $catalogInfo;
	}

    public function getCatalogInfo() {
        return $this->catalogInfo;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	//TODO
	public function doInsert($sql,array $sqlData) {
	}

	public function doUpdate($sql, array $sqlData) {
		DBCatalogHelper::getConn()->write($sql, $sqlData);
		return DBCatalogHelper::getConn()->getAffectedRows();
	}

    public function doGet($sql, array $sqlData) {
		$this->catalogInfo = DBCatalogHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->catalogInfo;
    }
}
