<?php
namespace Snake\Package\Shop;

Use \Snake\Package\Shop\Helper\DBShopTopicMapHelper;
use \Snake\libs\Cache\Memcache;

class ShopTopicMapMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('shop_id');	
	private $shopTopicMap = array();

    public function __construct($shopTopicMap = array()) {
		parent::__construct($this->enforce);
		$this->shopTopicMap = $shopTopicMap;
	}

    public function getShopTopicMap() {
        return $this->shopTopicMap;
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
		$this->shopTopicMap = DBShopTopicMapHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->shopTopicMap;
    }
}
