<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\GroupUser;

class Follow_groups extends \Snake\Libs\Controller {

	private $groupIds = NULL;
	private $userId = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$groupUserHelper = new GroupUser();
		$this->view = $groupUserHelper->operationFollowGroups($this->userId, $this->groupIds);
		return TRUE;
	}
	
	private function _init() {
		$this->groupIds = isset($this->request->REQUEST['group_ids']) ? $this->request->REQUEST['group_ids'] : 0 ;
		$this->userId = $this->userSession['user_id'];
		$this->groupIds = explode(',', $this->groupIds);

		if (empty($this->groupIds) || !is_array($this->groupIds)) {
			$this->setError(400, 40301, 'groupId is empty!');
			return FALSE;
		}
		if (empty($this->userId)) {
			$this->setError(400, 40101, 'userId is empty!');
			return FALSE;
		}
		/*$groupHelper = new Groups();
		//$groupInfo = $groupHelper->getGroupInfo(array($this->groupId));
		if (empty($groupInfo)) {
			$this->setError(400, 40303, "this group doesn't exist!");
			return FALSE;
		}*/
		return TRUE;

	}
	
}
