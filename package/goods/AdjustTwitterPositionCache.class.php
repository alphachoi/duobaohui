<?php
namespace Snake\Package\Goods;

Use \Snake\libs\Cache\Memcache;

/**
 * 类目页 位置 推的展示数统计 cache
 * @author xuanzheng
 * @package goods
 */
class AdjustTwitterPositionCache {

	/**
	 * profix
	 * @const string
	 */
	const CacheKey = "modules_goods_adjust_twitter_position:";

	private $cacheHelper = NULL;

	/**
	 * 存储推荐内容
	 * @var array
	 */
	private $recommendData = array();

	/**
	 * tid cachekey的唯一标示
	 * @var int
	 */
	private $tid = 0;
	private $p = 0;

	private $cacheTime = 2592000;

	public function __construct($tid = 0, $p) {
		$this->cacheTime = 3600 * 24 * 30;
		$this->tid = (int)$tid;
		$this->p = (int)$p;
		$this->cacheHelper = Memcache::instance();	
	}
	
	public function setTid($tid = 0) {
		$this->tid = $tid;			
		return TRUE;
	}

	public function setP($p = 0) {
		$this->p = $p;
		return TRUE;
	}



	/**
	 * set cache
	 * @param array
	 * @return boolean
	 */
	public function setCache($num = 0) {
		if (empty($num)) {
			return FALSE;
		}
		return $this->cacheHelper->set($this->getCacheKey(), $num, $this->cacheTime);
	}

	public function getCache() {
		$cacheData = $this->cacheHelper->get($this->getCacheKey());
		if (empty($cacheData)) {
			$cacheData = 0;	
		}
		return $cacheData;
	}

	private function getCacheKey() {
		$date = date("Y-m-d");
		return self::CacheKey . ":{$date}:{$this->p}:{$this->tid}";
	}

    
}
