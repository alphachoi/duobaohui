<?php
namespace Snake\Package\Goods;

Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\Helper\RedisNewestHelper;

class NewestViewTrace {

	public $cacheKey = NULL;
	public $cache = NULL;


	function __construct() {
		if (!empty($_COOKIE['MEILISHUO_GLOBAL_KEY'])) {
			$this->cacheKey =  "newest:{$_COOKIE['MEILISHUO_GLOBAL_KEY']}";
		}
		$this->cache = Memcache::instance();
	}


	public function putFirstTidIntoCache($tid = 0) {
		if ($tid == 0 || empty($this->cacheKey)) {
			return FALSE;	
		}
		$bool = $this->cache->set($this->cacheKey, $tid, 3600);
		return $bool;
	}

	public function getFirstTidFromCache() {
		if (empty($this->cacheKey)) {
			return FALSE;	
		}	
		$firstTid = $this->cache->get($this->cacheKey);
		return $firstTid;
	}

	public function clearFirstTidCache() {
		if (empty($this->cacheKey)) {
			return FALSE;	
		}	
		$bool = $this->cache->delete($this->cacheKey);
		return $bool;
	}

	public function getChangeSizeFromTid($tid = 0) {
		if (empty($tid)) {
			return FALSE;
		}
		return $this->getChangeSizeFromRedis($tid);
	  	//$changeSize = $this->getChangeSizeFromMysql($tid);
			
	}

	private function getChangeSizeFromRedis($tid) {
		$changeSize = RedisNewestHelper::getTwittersChange($tid);
		if (empty($changeSize)) {
			$changeSize = 0;	
		}
		return $changeSize;
	}


	private function getChangeSizeFromMysql($tid) {
	}
} 
