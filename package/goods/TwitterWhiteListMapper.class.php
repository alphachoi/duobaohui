<?php
namespace Snake\Package\Goods;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Goods\Helper\DBTwitterWhiteListHelper;
use \Snake\libs\Cache\Memcache AS Memcache;

class TwitterWhiteListMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('twitter_id');	
	private $twitterIds= array();

    public function __construct($twitterIds = array()) {
		parent::__construct($this->enforce);
		$this->twitterIds = $twitterIds;
	}

    public function getTwitterIds() {
        return $this->twitterIds;
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

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->twitterIds = DBTwitterWhiteListHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->twitterIds;
    }
}
