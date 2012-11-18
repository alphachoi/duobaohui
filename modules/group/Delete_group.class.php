<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\Groups AS Groups;

class Delete_group extends \Snake\Libs\Controller {

	private $groupId = NULL;
	private $userId = NULL;

	public function run() {
		if(!$this->_init()) {
			return FALSE;
		}
		$groupHelper = new Groups();
		$return = $groupHelper->deleteGroup($this->groupId, $this->userId);
		if ($return == TRUE) {
			$this->view = array("Delete Done!");
		}
		else {
			$this->view = array("Delete Failed!");
		}
		return TRUE;
	}

	private function _init() {
		$this->groupId = $this->request->REQUEST['group_id'];
		$this->userId = $this->userSession['user_id'];
		if (empty($this->groupId) || empty($this->userId) || !is_numeric($this->groupId)) {
			$this->setError(400, 40305, 'required parameter is empty');
			return FALSE;
		}
		return TRUE;
	}

}
