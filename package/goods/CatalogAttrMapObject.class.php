<?php
namespace Snake\Package\Goods;

class CatalogAttrMapObject extends \Snake\Package\Base\DomainObject{
	//数据库中的一行纪录
	private $catalogAttrMap = array();

    public function __construct($catalogAttrMap = array()) {
		$this->catalogAttrMap = $catalogAttrMap;
	}

    public function getCatalogAttrMap() {
        return $this->catalogAttrMap;
    }   
}
