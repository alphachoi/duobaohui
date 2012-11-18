<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser;

class Group_info extends \Snake\Libs\Controller {

	private $groupId = NULL;
	private $userId = NULL;
	private $superUsers = array(219,1751,1431119,1765,1698845,1590448,1714106,3896618, 7579460,7222759, 6140112, 10918214);
	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		
		$groupHelper = new Groups();
		$groupId = $this->groupId;
		$userId = $this->userSession['user_id'];
		$groupInfo = $groupHelper->getGroupInfo(array($groupId), array('group_id', 'name', 'description'));
		if (empty($groupInfo) || empty($userId)) {
			$this->view = array();
			return TRUE;
		}
		$groupUserHelper = new GroupUser();
		$roleInfo = $groupUserHelper->getGroupRelation(array($groupId), $userId);

		if ($roleInfo[$userId][0]['role'] != 1 && !in_array($userId, $this->superUsers)) {
			$this->view = array();
			return TRUE;
		}
		$groupInfo = $groupInfo[$groupId];
		unset($groupInfo['header_path']);
		$this->view = $groupInfo;
		return TRUE;

	}

	private function _init() {
		$this->groupId = $this->request->REQUEST['group_id'];
		if (empty($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty!');
			return FALSE;	
		}
		return TRUE;
	}

}
