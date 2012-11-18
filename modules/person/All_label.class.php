<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，所有用户label, 向添加label的弹出框提供4屏数据
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Label\Label as PLabel;			
Use \Snake\Libs\Cache\Memcache;

class All_label extends \Snake\Libs\Controller {
	private $visitedUserId = NULL;
	private $cache = 0; //TRUE;

	public function run()  {
		$labelInfos = $this->_getUserLabel();
		if (empty($labelInfos)) {
			$this->view = array();
			return FALSE;
		}
		//print_r($userMedal);exit;
		$this->view = $labelInfos;
	}
	
	private function _init() {
		//current login userId
		$this->visitedUserId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		return $this->visitedUserId;
	}
	
	/**
	 *	cache handle
	 **/
	private function _getUserLabel() {
		//$cacheKey = 'person:label_' . $this->visitedUserId;
		//$cacheHelper = Memcache::instance();
		//$userInfo = $cacheHelper->get($cacheKey);
		/*if ($this->cache && !empty($userInfo)) {
			return $userInfo;
		}*/
		$userLabel = PLabel::getInstance()->getLabelInfoByType(5);
		if (empty($userLabel)) {
			//$this->setError(400, 40104, 'user is not exist or user medal is empty');
			return FALSE;
		}
		//$cacheHelper->set($cacheKey, $userLabel, 3600);
		return $userLabel;
	}

}
