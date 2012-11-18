<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class CatalogGroupIcoObject extends \Snake\Package\Base\DomainObject{

    public function __construct($CatalogGroupIco = array()) {
		$this->row = $CatalogGroupIco;
	}
	
}
