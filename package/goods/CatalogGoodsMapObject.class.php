<?php
namespace Snake\Package\Goods;

class CatalogGoodsMapObject extends \Snake\Package\Base\DomainObject{

    public function __construct($catalogGoodsMap = array()) {
		$this->row = $catalogGoodsMap;
	}

}
