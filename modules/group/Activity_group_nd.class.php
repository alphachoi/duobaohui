<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\Groups;
Use \Snake\libs\Cache\Memcache;

class Activity_group_nd extends \Snake\Libs\Controller {

	private $groupIds = array(14578704, 14117276, 16098345, 15023235, 14449541, 15563927, 14861545, 48918, 15276219, 14162116, 14461876, 13692933);
	private $userId = 0;

	public function run() {
		if (!$this->_init()) {
			$this->view = array();
			return FALSE;
		}
		if (empty($this->userId)) {
			$cacheHelper = Memcache::instance();
			$cacheKey = "Activity_group_nd:1698845";
			$groupInfos = $cacheHelper->get($cacheKey);
			if (!empty($groupInfos)) {
				$this->view = $groupInfos;
				return TRUE;
			}
		}
		
		$groupHelper = new Groups();
		$groupInfos = $groupHelper->getGroupSquareInfo($this->groupIds, $this->userId);
		$groupInfos = array_values($groupInfos);
		$this->view = $groupInfos;
		if (!empty($groupInfos) && empty($this->userId)) {
			$cacheHelper->set($cacheKey, $groupInfos, 3600);
		}
		return TRUE;

	}

	private function _init() {
		$this->userId = isset($this->userSession['user_id']) ? $this->userSession['user_id'] : 0;
		return TRUE;
	}

}


