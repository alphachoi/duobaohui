<?php
namespace Snake\Package\Shop;
use \Snake\Libs\Base\Face;

class ShopExtInfoObject extends \Snake\Package\Base\DomainObject{

    public function __construct($shopExtInfo = array()) {
		$this->row = $shopExtInfo;
	}

}
