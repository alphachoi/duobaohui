<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\GroupUser;
use \Snake\Package\Group\Groups;

class Quit extends \Snake\Libs\Controller {

	private $groupId = NULL;
	private $userId = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$userId = $this->userSession['user_id'];
		$groupId = $this->groupId;
		$groupUserHelper = new GroupUser();
		$this->view = $groupUserHelper->operationQuit($userId, $groupId); 
		return TRUE;
	}
	
	private function _init() {
		$this->groupId = isset($this->request->REQUEST['group_id']) ? $this->request->REQUEST['group_id'] : 0 ;
		$this->userId = $this->userSession['user_id'];
		if (empty($this->groupId) || !is_numeric($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty!');
			return FALSE;
		}
		if (empty($this->userId)) {
			$this->setError(400, 40101, 'userId is empty!');
			return FALSE;
		}
        $groupHelper = new Groups();
        $groupInfo = $groupHelper->getGroupInfo(array($this->groupId));
        if (empty($groupInfo)) {
            $this->setError(400, 40303, "this group doesn't exist!");
            return FALSE;
        }
		return TRUE;

	}
	
}
