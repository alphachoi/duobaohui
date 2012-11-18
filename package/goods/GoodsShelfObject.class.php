<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class GoodsShelfObject extends \Snake\Package\Base\DomainObject{

    public function __construct($goodsShelf = array()) {
		$this->row = $goodsShelf;
	}
	
}
