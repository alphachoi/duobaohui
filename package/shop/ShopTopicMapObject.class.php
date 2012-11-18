<?php
namespace Snake\Package\Shop;
use \Snake\Libs\Base\Face;

class ShopTopicMapObject extends \Snake\Package\Base\DomainObject{

    public function __construct($shopTopicMap = array()) {
		$this->row = $shopTopicMap;
	}

}
