<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class GoodsReportObject extends \Snake\Package\Base\DomainObject{

    public function __construct($goodsReport = array()) {
		$this->row = $goodsReport;
	}

}
