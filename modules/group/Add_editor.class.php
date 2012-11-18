<?php
namespace Snake\Modules\Group;
/**
 * for bussiness activity, add current user as group editor
 * @author Chen Hailong
 */

use \Snake\Package\Group\GroupUser;

class Add_editor extends \Snake\Libs\Controller {

	private $groupId = NULL;
	private $specialGroupIds =  array(16169578);
	private $userId = NULL;
	public function run() {
		return FALSE;
		if (!$this->_init()) {
			return FALSE;
		}
		$groupUserHelper = new GroupUser();

		$check = $groupUserHelper->isGroupMember($this->groupId, $this->userId);
		if (!empty($check)) {
			$this->view = array('status' => 2, 'msg' => $this->userId . ' is already the editor of group:' . $this->groupId);
			return TRUE;
		}
		$return = $groupUserHelper->insertGroupUser($this->userId, $this->groupId, 0);
		$this->view = array('status' => 1);
		return TRUE;

	}

	private function _init() {
		$this->groupId = $this->request->REQUEST['group_id'];
		if (empty($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty!');
			return FALSE;	
		}
		if (!in_array($this->groupId, $this->specialGroupIds)) {
			$this->setError(400, 40303, 'groupId is illeagle!');
			return FALSE;	
		}
		if (empty($this->userSession['user_id'])) {
			$this->setError(400, 40302, 'Please login first');
			return FALSE;	
		}
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}
} //end of class Add_editor
