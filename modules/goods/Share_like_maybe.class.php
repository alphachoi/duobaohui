<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\ShareAttrRecommend;
Use Snake\Package\Recommend\Recommend;
Use Snake\Package\Goods\ShareLikeMaybeCache;

/**
 * Share页,猜你喜欢,数据接口
 * @author xuanzheng
 * @package goods
 * @request_url http://snake.meilishuo.com/goods/share_like_maybe?tid=***
 * @request_method GET
 * @request_param tid : 0
 */
Class Share_like_maybe extends \Snake\Libs\Controller {

	private $cacheSwitch = TRUE;

	/**
	 * twitter id 
	 * @var int
	 */
	private $tid = 0;

	private $cacheHelper = NULL;


	/**
	 * init data to response
	 * @var array
	 */
	private $response = array();

	/**
	 * RT , It's initialized method 
	 * now, get tid only 
	 * @param NULL
	 * @return TRUE
	 */
	private function initialized() {
		$this->tid = (int)$this->request->REQUEST['tid'];
		$this->cacheHelper = new ShareLikeMaybeCache($this->tid);
		return TRUE;
	}

	/**
	 * run! run!! run!!!
	 * @return TRUE
	 */
	public function run() {
		$this->initialized();
		$this->getCacheData();
		$this->getDataWithNoCache();
		$this->view = $this->response;
		return TRUE;	
	}

	private function getDataWithNoCache() {
		if (!empty($this->response)) {
			return FALSE;	
		}
		$attrRecommendHelper = new ShareAttrRecommend($this->tid);
		$responseData = $attrRecommendHelper->shareAttrRecommendMaybeLie();
		if (!empty($responseData)) {
			$this->response = $responseData;
			$this->setCacheData($this->response);
		}
		return TRUE;
	}

	private function getCacheData() {
		$response =	$this->cacheHelper->getCache();
		if (!empty($response) && $this->cacheSwitch) {
			$this->response = $response;	
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
