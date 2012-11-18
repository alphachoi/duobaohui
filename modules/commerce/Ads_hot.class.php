<?php

namespace Snake\Modules\Commerce;

Use Snake\Package\Commerce\AdsData;
Use Snake\Package\Goods\Catalog;
Use Snake\Libs\Cache\Memcache;

class Ads_hot extends \Snake\Libs\Controller {

	private $cataId    = 0;
	private $wordName    = '';
	private $frame	   = 0;
	private $page	   = 0;
	private $offset    = 0;
	private $countSize = NULL;



	public function run () {
		if (!$this->_init()) {
			return FALSE;	
		}

		$adsData = new AdsData();
		$totalAdsData = $adsData->getAdsData($this->pageType, $this->cataId, $this->wordName, $this->page);

		$ads_hot = $totalAdsData['common_ads'];

		if (!empty($ads_hot)) {
			$ads_hot = array_slice($ads_hot, $this->offset, $this->countSize);
		}
		if (!empty($ads_hot)) {
			foreach ($ads_hot as $k => $v) {
				$ads_hot[$k]['ads_column'] = $totalAdsData['ads_column'];
			}
		}
		if (!empty($ads_hot)) {
			$this->view = $ads_hot;
			return TRUE;
		}
		else {
			$this->view = array();
			return TRUE;
		}
	}


	/**
	 * 初始化变量
	 */
	private function _init() {
		if (!$this->setPage()) {
			return FALSE;
		}
		if (!$this->setWordName()) {
			return FALSE;
		}
		if (!$this->setCataId()) {
			return FALSE;
		}
		if (!$this->setPageType()) {
			return FALSE;
		}
		if (!$this->setOffset()) {
			return FALSE;
		}
		if (!$this->setCount()) {
			return FALSE;
		}
		return TRUE;
	}


	private function setPageType() {
		$this->pageType = 0;
		if (empty($this->cataId) && !empty($this->wordName)) {
			$this->pageType = 3;
		}
		if (!empty($this->cataId) && empty($this->wordName)) {
			$this->pageType = 1;
		}
		if (!empty($this->cataId) && !empty($this->wordName)) {
			$this->pageType = 2;
		}
		if (empty($this->pageType)) {
			$this->errorMessage(400, 'bad pageType');
			return FALSE;
		}	
		return TRUE;
	}


	private function setCataId() {
		$cataId = intval($this->request->REQUEST['cata_id']);
		if (!empty( $cataId ) && !is_numeric( $cataId )) {
			$cataId = 0;
		}
		$isCatalogId = Catalog::isCatalogTab($cataId);
		if (!$isCatalogId) {
			$cataId = 0;
		}
		$this->cataId = $cataId;
		return TRUE;
	}


	private function setWordName() {
		$wordName = $this->request->REQUEST['word'];
		$this->wordName = $wordName;
		return TRUE;
	}


	private function setPage() {
		$page = intval($this->request->REQUEST['page']);
		if (!isset($page) || !is_numeric($page)) {
			$this->errorMessage(400, 'bad page');
			return FALSE;
		}
		if ($page < 0)  {
			$this->errorMessage(400, 'page is negative');
			return FALSE;
		}
		$this->page = $page;
		return TRUE;
	}


	private function setOffset() {
		$offset = intval($this->request->REQUEST['offset']);
		if (!empty($offset) && !is_numeric($offset)) {
			$this->errorMessage(400, 'offset is not number');
			return FALSE;
		}
		$this->offset = $offset;
		return TRUE;
	}


	private function setCount() {
		$count = intval($this->request->REQUEST['count']);
		if (!empty($count) && !is_numeric($count)) {
			$this->errorMessage(400, 'count is not number');
			return FALSE;
		}
		if (!empty($count)) {
			$this->countSize = $count;
		}
		return TRUE;
	}


	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}

}
