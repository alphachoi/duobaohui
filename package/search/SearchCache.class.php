<?php
namespace Snake\Package\Search;

Use \Snake\libs\Cache\Memcache;

class SearchCache {

//	private $memcache = null;
//	private $memcacheKey = NULL;	
//	protected $searhObj = NULL;	
	
//    public function __construct(\Snake\Package\Search\SearchObject $searchObj) {
//		$this->memcache = Memcache::instance();
//		$this->searchObj = $searchObj;
//	}
	const cacheTime = 600;
	const profix = 'Search:';

	static public function setSearch(\Snake\Package\Search\SearchObject $searchObj, $values) {
		$searchCache = new self();
		$cacheKey = $searchCache->getCacheKey($searchObj);
		$memcache = Memcache::instance();
		$cacheSetBool = FALSE;
		if (!empty($values['matches'])) {
			$cacheSetBool = $memcache->set($cacheKey, $values, 10000);
		}
		$cacheContent = $memcache->get($cacheKey);
		return $cacheSetBool;
	}

	static public function getSearch(\Snake\Package\Search\SearchObject $searchObj) {
		$searchCache = new self();
		$cacheKey = $searchCache->getCacheKey($searchObj);
		$memcache = Memcache::instance();
		$cacheContent = $memcache->get($cacheKey);
		return $cacheContent;
	}

	private function getCacheKey(\Snake\Package\Search\SearchObject $searchObj) {
		$md5Key = md5($searchObj);
		$cachekey = self::profix . "index:" . $searchObj->getIndex() . ":" . $md5Key;
		return $cachekey;
	}
}
