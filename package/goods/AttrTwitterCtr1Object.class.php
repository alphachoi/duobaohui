<?php
namespace Snake\Package\Goods;
use \Snake\Libs\Base\Face;

class AttrTwitterCtr1Object extends \Snake\Package\Base\DomainObject{
	//数据库中的一行纪录
	private $twitterIds= array();

    public function __construct($twitterIds = array()) {
		$this->twitterIds = $twitterIds;
	}

    public function getTwitterIds() {
        return $this->twitterIds;
    }   
}
