<?php
namespace Snake\Package\Shop;

Use \Snake\Package\Shop\Helper\DBShopExtInfoHelper;
use \Snake\libs\Cache\Memcache;

class ShopExtInfoMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('shop_id');	
	private $shopExtInfo = array();

    public function __construct($shopExtInfo = array()) {
		parent::__construct($this->enforce);
		$this->shopExtInfo = $shopExtInfo;
	}

    public function getShopExtInfo() {
        return $this->shopExtInfo;
    }   

    public function router($method, $prefix, $extra = array()) {
        $goto = $method . '__' . $prefix;
        return call_user_func(array($this, $goto), $extra);
    }
	//TODO
	public function doInsert($sql,array $sqlData) {
	}
	//TODO
	public function doUpdate() {
	}


    public function doGet($sql, array $sqlData) {
		$this->shopExtInfo = DBShopExtInfoHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->shopExtInfo;
    }
}
