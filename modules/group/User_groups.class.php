<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\GroupUser;

class User_groups extends \Snake\Libs\Controller {
	
	private $userId = 0;
	public function run() {
		if (!$this->_init()) {
			$this->view = array();
			return FALSE;
		}
		$groupUserHelper = new GroupUser();
		$this->view = $groupUserHelper->getUserGroups($this->userId);
		return TRUE;
	}

	private function _init() {
		if (empty($this->userSession['user_id'])) {
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}
}

