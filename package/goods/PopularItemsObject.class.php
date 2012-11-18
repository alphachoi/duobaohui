<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class PopularItemsObject extends \Snake\Package\Base\DomainObject{

    public function __construct($popularItems = array()) {
		$this->row = $popularItems;
	}
}
