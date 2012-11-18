<?php
namespace Snake\Package\Goods;

Use \Snake\libs\Cache\Memcache;

/**
 * 类目页 展示方案展示数统计 cache
 * @author xuanzheng
 * @package goods
 */
class TopTidCatalogCoutCache {

	/**
	 * profix
	 * @const string
	 */
	const CacheKey = "modules_goods_top_twitter_plan_new:";

	private $cacheHelper = NULL;

	/**
	 * plan name cachekey的唯一标示
	 * @var int
	 */
	private $plan = '';
	private $cacheTime = 2592000;

	public function __construct($plan = '') {
		$this->cacheTime = 3600 * 24 * 30;
		$this->plan = $plan;
		$this->cacheHelper = Memcache::instance();	
	}
	
	public function setPlan($plan = '') {
		$this->plan = $plan;			
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
		return self::CacheKey . ":{$date}:{$this->plan}";
	}

    
}
