<?php
namespace Snake\Package\Twitter;

//Use \Snake\Libs\PlatformService\MlsStorageService;
//Use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Twitter\Helper\DBTwitterStatHelper;
//Use \Snake\libs\Cache\Memcache AS Memcache;
require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');

class TwitterStatMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('twitter_id','twitter_author_uid','twitter_goods_id');	
	private $twitter = array();

    public function __construct($twitter = array()) {
		parent::__construct($this->enforce);
		$this->twitter = $twitter;
	}

    public function getTwitterStat() {
        return $this->twitter;
    }   

	public function doInsert($sql, array $sqlData) {
		DBTwitterStatHelper::getConn()->write($sql, $sqlData);
		$id = DBTwitterStatHelper::getConn()->getInsertId();
		return $id;
	}

	//TODO
	public function doUpdate() {
	}

	/*protected function doCreateCollection(array $twitters) {
		$collection = new TwitterCollection($twitters, $this);
		return $collection;
	}*/

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->twitter = DBTwitterStatHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->twitter;
    }
    
}
