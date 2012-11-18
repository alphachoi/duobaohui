<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\ShareAttrRecommend;
Use Snake\Package\Goods\ShareGoodsRecommendCache;

/**
 * Share页,也许你还喜欢,数据接口
 * @author xuanzheng
 * @package goods
 * @request_url http://snake.meilishuo.com/goods/share_goods_recommend?tid=***
 * @request_method GET
 * @request_param tid : 0
 */

class Share_goods_recommend extends \Snake\Libs\Controller {

	/**
	 * switch for cache
	 * @var boolean
	 */
	private $cacheSwitch = TRUE;

	/**
	 * twitter id 
	 * @var int
	 */
	private $tid = 0;

	/**
	 * init data to response
	 * @var array
	 */
	private $response = array();

	/**
	 * It's initialized method 
	 * now, get tid only 
	 * @param NULL
	 * @return TRUE
	 */
	private function initialized() {
		$this->tid = (int)$this->request->REQUEST['tid'];
		$this->cacheHelper = new ShareGoodsRecommendCache($this->tid);
		return TRUE;
	}

	/**
	 * api start
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
		$attrRecommend = new ShareAttrRecommend($this->tid);
		$responseData = $attrRecommend->shareGoodsRecommend();
		if (!empty($responseData)) {
			$this->response = array_slice($responseData,0, 6);
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
