<?php

namespace Snake\Modules\Commerce;

Use Snake\Package\Commerce\AdsData;
Use Snake\Package\User\User;
Use Snake\Package\User\UserRelation;
Use Snake\Package\Goods\Catalog;

class Ads_mall extends \Snake\Libs\Controller {

	private $cataId   = 0;
	private $wordName   = 0;
	private $frame	  = 0;
	private $page	  = 0;
	private $pageType = 0;
	private $userId	  = 0;

	public function run () {
		if (!$this->_init()) {
			return FALSE;	
		}
		$adsData = new AdsData();
		$totalAdsData = $adsData->getAdsData($this->pageType, $this->cataId, $this->wordName, $this->page);
		$ads_mall = $totalAdsData['mall_ads'];
		if (!empty($ads_mall)) {
			$ads_data = array();
			$userId = array();
			foreach ($ads_mall as $u) {
				$userId[] = $u['extid'];
			}
			$userHelper = new UserRelation();
			$follow	= $userHelper->getUserRelation($this->userId, $userId); 
			foreach ($ads_mall as $k => $v ) {
				$ads_data[$k]['user_id'] = $v['extid']; 
				$ads_data[$k]['nickname'] = $v['intro']; 
				$ads_data[$k]['avatar_c'] = $v['pic_url']; 
				$ads_data[$k]['mall_url'] = urldecode($v['url']); 
				$ads_data[$k]['isFollow'] = isset($follow[$v['extid']]) ? $follow[$v['extid']] : 0;
			}
			$this->view = $ads_data;
		}
		else {
			$this->view = array();
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
		if (!$this->setUserId()) {
			return FALSE;
		}
		if (!$this->setPageType()) {
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


	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
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


	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}



}
