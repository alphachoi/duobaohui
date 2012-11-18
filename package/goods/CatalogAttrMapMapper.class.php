<?php
namespace Snake\Package\Goods;

Use \Snake\Package\Goods\Helper\DBCatalogHelper;

class CatalogAttrMapMapper extends \Snake\Package\Base\Mapper{
	private $catalogAttrMapInfo =  array();

    public function __construct($catalogAttrMapInfo = array()) {
		$this->catalogAttrMapInfo = $catalogAttrMapInfo;
	}

    public function getCatalogAttrMapInfo() {
        return $this->catalogAttrMapInfo;
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
		$this->catalogAttrMapInfo = DBCatalogHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->catalogAttrMapInfo;
    }
}
