<?php
namespace Snake\Modules\Goods;

Use \Snake\Package\Goods\Catalog;
Use \Snake\Package\Goods\Tag;
Use \Snake\Package\Goods\Registry;
Use \Snake\Package\Goods\PosterCatalogRequest;
Use \Snake\Package\Manufactory\Poster;
Use \Snake\Libs\Cache\Memcache;

class Catalog_totalnum extends \Snake\Libs\Controller {

	const maxFrame = FRAME_SIZE_MAX; 
	const pageSize = WIDTH_PAGE_SIZE;
	const isShowClose = 0;
	const isShowLike = 1;

	private $posterRequest = NULL;
	private $responsePosterData = array('totalNum' => 0);
	private $cacheHelper = NULL;
	private $cacheSwitch = TRUE;

	private function initialize() {
		$this->setRegistry();
		$this->cacheHelper = Memcache::instance();
		return TRUE;
	}

	private function setRegistry() {
		$registry = Registry::instance();
		$registry->setRequest(new PosterCatalogRequest());
		$this->posterRequest = $registry->getRequest();
		return TRUE;
	}

	private function setRequest($request) {
		$request->setPage($this->request->REQUEST['page']);
		$request->setFrame($this->request->REQUEST['frame']);
		$request->setWordId($this->request->REQUEST['word']);
		$request->setCataId($this->request->REQUEST['cata_id']);
		$request->setOrderBy($this->request->REQUEST['section']);
		$request->setPrice($this->request->REQUEST['price']);
		$request->setMaxFrame(self::maxFrame);
		$request->setPageSize(self::pageSize);
		$request->setUserId($this->userSession['user_id']);
		return TRUE;
	}
		
	private function checkRequest($request) {
		$request->checkRequest();
		if ($request->error()) {
			$error = $request->getErrorData();
			self::setError(400, $error['errorCode'], $error['errorMessage']);
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * interface()
	 */
	public function run() {
		$this->initialize();
		$this->setRequest($this->posterRequest);
		if (!$this->checkRequest($this->posterRequest)) {
			$this->view = $this->responsePosterData;
			return FALSE;	
		}
		if (!$this->useCache()) {
			$this->nouseCache();	
			$this->setCache();
		}
		$this->view = $this->responsePosterData;
		return TRUE;
	}

	private function useCache() {
		$useCache = FALSE;
		$cacheKey = $this->getCacheKey();
		$responsePosterDataFromCache = $this->cacheHelper->get($cacheKey);
		if (!empty($responsePosterDataFromCache) && $this->cacheSwitch) {
			$useCache = TRUE;
			$this->responsePosterData = $responsePosterDataFromCache;
		}
		return $useCache;
	}

	private function nouseCache() {
		$catalogObj = new Catalog();
		$catalogObj->Search();
		$totalNum = $catalogObj->getTotalNum();

		$this->responsePosterData['totalNum'] = $totalNum;
		return TRUE;
	}

	private function setCache() {
		$setCacheOk = FALSE;
		$userId = $this->posterRequest->getUserId();
		$setCache = !empty($this->responsePosterData['totalNum']) && $this->cacheSwitch;
		if ($setCache) {
			$cacheKey = $this->getCacheKey();
			$setCacheOk = $this->cacheHelper->set($cacheKey, $this->responsePosterData, 600);
		}
		return $setCacheOk;
	}

	private function getCacheKey() {
		$cacheKeySuffixArray = (array)$this->posterRequest;
		foreach ($cacheKeySuffixArray as $key => $content) {
			if (is_array($content)) {
				$content = implode("_", $content);	
			}
			$cacheKeySuffix .= "{$key}_{$content},";
		}
		$cacheKeySuffix = md5($cacheKeySuffix);
		$cacheKey = "CACHE_CATALOG_TOTALNUM:" . $cacheKeySuffix;
		return $cacheKey;
	}

	public function getPosters($tids = array(), $userId = 0) { 
		$posterObj = new Poster();
		$posterObj->isShowLike(self::isShowLike);
		$posterObj->isShowClose(self::isShowClose);
		$posterObj->setVariables($tids, $userId);
		$poster	= $posterObj->getPoster();
		if (empty($poster)) {
			$poster = array();
		}
		return $poster;
	}


	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}
}
