<?php
namespace Snake\Package\Verify;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Verify\Helper\DBTwitterVerifyHelper;
use \Snake\libs\Cache\Memcache AS Memcache;

class TwitterVerifyMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('twitter_id');	
	private $twitterVerify = array();

	public function __construct($twitterVerify = array()) {
		parent::__construct($this->enforce);
		$this->twitterVerify = $twitterVerify;
	}

	public function getTwitterVerify() {
		return $this->twitterVerify;
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
		$this->twitterVerify = DBTwitterVerifyHelper::getConn()->read($sql, $sqlData, $this->master, $this->key); 
		return $this->twitterVerify;
	}


}
