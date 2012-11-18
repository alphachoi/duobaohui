<?php
namespace Snake\Package\Goods;

Use \Snake\libs\Cache\Memcache;

/**
 * 单推页属性推荐 猜你喜欢cache
 * @author xuanzheng
 * @package goods
 */
class ShareLikeMaybeCache{

	/**
	 * profix
	 * @const string
	 */
	const CacheKey = "modules_goods_share_like_maybe:";

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

	private $cacheTime = 600;

	public function __construct($tid = 0) {
		$this->tid = (int)$tid;
		$this->cacheHelper = Memcache::instance();	
	}



	/**
	 * 一个抽象method
	 * @param NULL
	 * @return NULL
	 */
	public function put() {
	}

	/**
	 * set cache
	 * @param array
	 * @return boolean
	 */
	public function setCache($recommendData = array()) {
		if (empty($recommendData)) {
			return array();
		}
		return $this->cacheHelper->set($this->getCacheKey(), $recommendData, $this->cacheTime);
	}

	public function getCache() {
		$cacheData = $this->cacheHelper->get($this->getCacheKey());
		if (empty($cacheData)) {
			$cacheData = array();	
		}
		return $cacheData;
		
	}

	public function del() {
	
	}

	private function getCacheKey() {
		return self::CacheKey . $this->tid;
	}

    
}
