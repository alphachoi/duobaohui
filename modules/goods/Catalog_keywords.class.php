<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;
Use Snake\Libs\Cache\Memcache;

/**
 * @example 
 * curl snake.mydev.com/goods/catalog_keywords?cata_id=5000000000000 
 *
 * @author xuanzheng 
 */


class Catalog_keywords extends \Snake\Libs\Controller  {

	private $cataId = 0;
	private $wordId = 0;
	private $useCacheForPosters = TRUE;
	private $catalogTab = array(
		2000000000000, 2001000000000,
		2004000000000, 2006000000000,
		2009000000000, 6000000000000,
		5000000000000, 7000000000000,
		8000000000000, 9000000000000
		);


	/**
	 *初始化变量
	 */
	public function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}
		if (!$this->setCataId()) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 *you know, it's runing...
	 */
	public function run() {
		$cataId = intval($this->request->REQUEST['cata_id']);
		if (!$this->_init()) {
			return FALSE;
		}

		$cacheHelper = Memcache::instance();
		$cacheMd5 = md5("{$this->wordId}_{$this->cataId}");
		$cacheKeyForKeywords = "CacheKey:Catalog_keywords:{$cacheMd5}";
		$keywordsFromCache = $cacheHelper->get($cacheKeyForKeywords);

		if ( !empty($keywordsFromCache) && $this->useCacheForPosters) {
			$keywords = $keywordsFromCache;
		}
		else {
			$keywords = AttrWords::getkeywords($this->cataId, $this->wordId);
			if (!empty($keywods['sub']['group_map']) && !empty($keywods['sub']['group_keywords'])) {
				$cacheHelper->set($cacheKeyForKeywords, $keywords, 1200);
			}
		} 

		if (!empty($keywords) ) {
			$this->view = $keywords;
		}
		else {
			$this->view = array(); 
		}
	}


	private function setCataId() {
		$cataId = intval($this->request->REQUEST['cata_id']);
		if (empty($cataId) || !is_numeric($cataId)) {
			$this->errorMessage(400, 'bad cataId');
			return FALSE;
		}
		$isCatalogId = in_array($cataId, $this->catalogTab);
		if (!$isCatalogId) {
			$this->errorMessage(400, 'cataId is not catalog');
			return FALSE;
		}
		$this->cataId = $cataId;
		return TRUE;
	}


	private function setWordId() {
		$wordId = intval($this->request->REQUEST['word']);
		if (!empty($wordId) && !is_numeric($wordId)) {
			$this->errorMessage(400, 'word is not number');
			return FALSE;
		}
		if ($wordId < 0) {
			$this->errorMessage(400, 'bad word');
			return FALSE;
		}
		$this->wordId = $wordId;
		return TRUE;
	}


	/**
	 *感觉这东西放在controller里好一些,jianxu~~~
	 */
	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}


} 
