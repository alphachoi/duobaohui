<?php
/**
 Groupheader.class.php
 */

namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupTwitters;
use \Snake\Package\Manufactory\Poster AS Poster;
use \Snake\package\Group\GroupUser AS GroupUser;
use Snake\Package\Recommend\Recommend;

class Recommend_group extends \Snake\Libs\Controller {

	private $groupId = NULL;

	public function run() {
		if (!$this->init()) {
			return FALSE;
		}
		
		$recommendHelper = new Recommend();
		//$groupIds = $recommendHelper->getReGroup(6, $this->groupId, $this->userSession);
		$groupIds = $recommendHelper->getReGroupByGroupid($this->groupId, $this->userSession['user_id']);
		$gIds = array();
		if (empty($groupIds) || !is_array($groupIds)) {
			return array();
		}
		foreach ($groupIds AS $groupId) {
			$gIds[] = $groupId['group_id'];
		}		

		$groupHelper = new Groups();
		$groupTwitterHelper = new GroupTwitters();
		$groupPic = $groupTwitterHelper->getGroupTwitterPic($gIds);
		if (!empty($groupPic)) {
			$groupPic = array_slice($groupPic, 0, 4);
		}
		if (empty($groupPic)) {
			$this->view = array();
		}
		else {
			$this->view = $groupPic;
		}
		return TRUE;

	}

	private function init() {
		$this->groupId = intval($this->request->REQUEST['group_id']);
		if (empty($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty');
			return FALSE;
		}
		return TRUE;
	}
}
	
