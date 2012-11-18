<?php
namespace Snake\Package\Twitter;

Use \Snake\Libs\PlatformService\MlsStorageService;
Use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Twitter\Helper\DBTwitterHelper;
Use \Snake\libs\Cache\Memcache AS Memcache;
require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');

class TwitterMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('twitter_id','twitter_author_uid','twitter_show_type');	
	private $twitter = array();

    public function __construct($twitter = array()) {
		parent::__construct($this->enforce);
		$this->twitter = $twitter;
	}

    public function getTwitter() {
        return $this->twitter;
    }   

	public function doInsert($sql, array $sqlData) {
		DBTwitterHelper::getConn()->write($sql, $sqlData);
		$id = DBTwitterHelper::getConn()->getInsertId();
		return $id;
	}

	//TODO
	public function doUpdate($sql, array $sqlData) {
		DBTwitterHelper::getConn()->write($sql, $sqlData);
		return DBTwitterHelper::getConn()->getAffectedRows();
	}

	//TODO 	
	/*public function update(){
	}*/

	/*protected function doCreateCollection(array $twitters) {
		$collection = new TwitterCollection($twitters, $this);
		return $collection;
	}*/

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->twitter = DBTwitterHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->twitter;
    }
	
	public function storageGet($table, $col, $sql) {
        $this->twitter = MlsStorageService::GetQueryData($table, NULL, $col, $sql);
		//错误处理
		if (!is_array($this->twitter) || empty($this->twitter)) {
			$this->twitter = array();
		}
		return $this->twitter;
	}
    
	public function storageMultiRowGet($table, $keyName, $keyVal, $filter, $forceIndex, $start, $limit, $orderBy, $orderDir, $columnNames, $hashKey = '') {
		$this->twitter = MlsStorageService::MultiRowGetUniq($table, $keyName, $keyVal, $filter, $forceIndex, $start, $limit, $orderBy, $orderDir, $columnNames, $hashKey);
		//错误处理
		if (!is_array($this->twitter) || empty($this->twitter)) {
			$this->twitter = array();
		}
		return $this->twitter;	
	}

	public function storageUniqRowGet($table, $keyName, $keyVals, $filter, $columns, $hashKey = "") {
		$this->twitter = MlsStorageService::UniqRowGetMultiKey($table, $columns, $keyName, $keyVals, $filter, $hashKey);
		//错误处理
		if (!is_array($this->twitter) || empty($this->twitter)) {
			$this->twitter = array();
		}
		return $this->twitter;	
	}

	public function storageQueryRead($sql, $hashKey = ""){
        $this->twitter = MlsStorageService::QueryRead($sql, $hashKey);
		//错误处理
		if (!is_array($this->twitter) || empty($this->twitter)) {
			$this->twitter = array();
		}
		return $this->twitter;
	}

}
