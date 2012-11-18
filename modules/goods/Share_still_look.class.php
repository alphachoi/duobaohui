<?php
namespace Snake\Modules\Goods;
Use Snake\Package\Goods\ShareAttrRecommend;
Use Snake\Package\Goods\ShareStillLookCache;

/**
 * Share页,MM还在看,数据接口
 * @author xuanzheng
 * @package goods
 * @request_url http://snake.meilishuo.com/goods/share_still_look?tid=***
 * @request_method GET
 * @request_param tid : 0
 */
class Share_still_look extends \Snake\Libs\Controller {

	/**
	 * twitter id
	 * @var int
	 */
	private $tid = 0;

	private $cacheSwitch = TRUE;

	private $cacheHelper = NULL;

	/**
	 * default response data
	 * @var array
	 */
	private $response = array();
	
	/**
	 * initialized
	 * @param NULL
	 * @return TRUE
	 */
	private function initialized() {
		$this->tid = (int)$this->request->REQUEST['tid'];
		$this->cacheHelper = new ShareStillLookCache($this->tid);
		return TRUE;	
	}

	/**
	 * just run
	 * @return TRUE 
	 */
	public function run() {
		$this->initialized();
		$this->getCacheData();
		$this->getDataWithNoCache();
		$this->view = $this->response;
		return TRUE;	
	}

	private function getCacheData() {
		$response =	$this->cacheHelper->getCache();
		if (!empty($response) && $this->cacheSwitch) {
			$this->response = $response;	
		}
		return TRUE;	
	}

	private function getDataWithNoCache() {
		if (!empty($this->response)) {
			return FALSE;
		}
		$attrRecommendHelper = new ShareAttrRecommend($this->tid);
		$responseData = $attrRecommendHelper->shareAttrRecommendSide();
		if (!empty($responseData)) {
			$this->response = $responseData;
			$this->setCacheData($this->response);
		}
		return TRUE;
	}

	private function setCacheData($response = array()) {
		$ok = FALSE;
		if (!empty($response) && $this->cacheSwitch) {
			$ok = $this->cacheHelper->setCache($response);
		}
		return $ok;
	}














}
