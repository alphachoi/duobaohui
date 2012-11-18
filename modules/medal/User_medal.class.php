<?php
namespace Snake\Modules\Medal;

/**
 * 个人页面，我的勋章
 * @author, Chen Hailong
 **/

Use \Snake\Package\Medal\Medal as PMedal;			
Use \Snake\Libs\Cache\Memcache;

class User_medal extends \Snake\Libs\Controller {
	private $visitedUserId = NULL;
	private $cache = 0; //TRUE;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$userMedal = $this->_getUserMedal();
		if (empty($userMedal)) {
			$this->view = array();
			return;
		}
		//print_r($userMedal);exit;
		$this->view = $userMedal;
	}
	
	private function _init() {
		//current login userId
		$this->visitedUserId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($this->visitedUserId)) {
			$this->setError(400, 40101, 'userId is empty');
			return FALSE;
		}
		return $this->visitedUserId;
	}
	
	/**
	 *	cache handle
	 **/
	private function _getUserMedal() {
		$cacheKey = 'person:medal_' . $this->visitedUserId;
		$cacheHelper = Memcache::instance();
		$userInfo = $cacheHelper->get($cacheKey);
		if ($this->cache && !empty($userInfo)) {
			return $userInfo;
		}
		$medalHelpper = new PMedal();
		$userMedal = $medalHelpper->getMedalByUid($this->visitedUserId);
		if (empty($userMedal)) {
			//$this->setError(400, 40104, 'user is not exist or user medal is empty');
			return FALSE;
		}

        foreach ($userMedal as $key => $value) {
            if (isset($userMedal[$key]['update_time'])) {
                $userMedal[$key]['update_time'] = date('m月d日G:i', strtotime($userMedal[$key]['update_time']));
            }   
        }

		$cacheHelper->set($cacheKey, $userMedal, 3600);
		return $userMedal;
	}

}
