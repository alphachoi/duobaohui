<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class GoodsVerifyObject extends \Snake\Package\Base\DomainObject{

    public function __construct($goodsVerify = array()) {
		$this->row = $goodsVerify;
	}
}
