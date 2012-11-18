<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，我的标签
 * @author, Chen Hailong
 **/

Use \Snake\Package\User\User;			
Use \Snake\Libs\Cache\Memcache;

class Label extends \Snake\Libs\Controller {
	private $visitedUserId = NULL;
	private $userId = NULL;
	private $cache = FALSE;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$user = new User();
		$userLabel = $this->_getUserLabel($user);
		if (empty($userLabel)) {
			$this->view = array();
			return FALSE;
		}
		//print_r($userLabel);exit;
		$this->view = $userLabel;
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
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
	private function _getUserLabel($user) {
		$cacheKey = 'person:label_' . $this->visitedUserId;
		$cacheHelper = Memcache::instance();
		$userInfo = $cacheHelper->get($cacheKey);
		if ($this->cache && !empty($userInfo)) {
			return $userInfo;
		}
		$userLabel = $user->getUserLabel($this->visitedUserId);
		if (empty($userLabel)) {
			//$this->setError(400, 40103, 'user is not exist or user label is empty');
			return FALSE;
		}
		$cacheHelper->set($cacheKey, $userLabel, 3600);
		return $userLabel;
	}

}
