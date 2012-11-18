<?php
namespace Snake\Modules\Group;

use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;


class Update_group extends \Snake\Libs\Controller {

	private $groupId = NULL;
	private $name = NULL;
	private $description = NULL;
	private $superUsers = array(219,1751,1431119,1765,1698845,1590448,1714106,3896618, 7579460,7222759, 6140112, 10918214);

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$groupId = $this->groupId;
		$userId = $this->userSession['user_id'];
		$groupUserHelper = new GroupUser();
		$userRole = $groupUserHelper->getGroupRelation(array($groupId), $userId);
		
		if ($userRole[$userId][0]['role'] != 1 && !in_array($userId, $this->superUsers)) {
			$this->setError(400, 40304, 'permission denied');
			return FALSE;
		}
		
		if (!empty($this->name)) {
			$params['name'] = $this->name;
		}
		if (!empty($this->description)) {
			$params['description'] = $this->description;
		}
		$groupHelper = new Groups();
		if (!empty($this->name)) {
			$result = $this->checkName($groupHelper);
			if ($result != TRUE) {
				$this->view = array(
					'error_code' => '杂志名称已经存在。'
				);
				return FALSE;
			}
		}

		$groupHelper->updateGroupInfo(array($groupId), $params);
		return TRUE;
	}

	private function _init() {
		if (empty($this->userSession['user_id'])) {
			$this->setError(400, 40101, 'userId is empty');
			return FALSE;
		}
		$this->groupId = isset($this->request->REQUEST['group_id']) ? $this->request->REQUEST['group_id'] : 0;
		if (empty($this->groupId) || !is_numeric($this->groupId)) {
			$this->setError(400, 40301, 'groupId is empty');	
			return FALSE;
		}
		$this->name = $this->request->REQUEST['name'];
		$maskWords = new \Snake\Package\Spam\MaskWords($this->name);
		$result = $maskWords->getMaskWords();
		if (!empty($result['maskWords'])) {
			$this->view = array(
				'error_code' => '杂志名称包含屏蔽词。'
			);
			return FALSE;
		}
		$this->name = htmlspecialchars_decode($this->name);
		$this->name = str_replace('\t' , '', $this->name);
		$this->name = trim($this->name);
		$this->name = mb_substr($this->name, 0, 20, 'utf-8');
		$this->name = preg_replace('/[\$|#|\|"|]/', '', $this->name);
		$this->name = htmlspecialchars($this->name);

		//$this->name = preg_replace('/[\$|&quot;|#|"| |]/', '', $this->name);
		if ($this->name != $this->request->REQUEST['name']) {
			$this->view = array(
				'error_code' => '杂志名称包含非法字符。'
			);
			return FALSE;
		}
		$this->description = (isset($this->request->REQUEST['description']) && !empty($this->request->REQUEST['description'])) ? $this->request->REQUEST['description'] : "爱美丽的杂志，大家一块来玩吧～～";
		$maskWords = new \Snake\Package\Spam\MaskWords($this->description);
		$result = $maskWords->getMaskWords();
		if (!empty($result['maskWords'])) {
			$this->view = array(
				'error_code' => '杂志卷首语包含屏蔽词。'
			);
			return FALSE;
		}
		$this->description = $result['maskedContent'];
		$this->description = mb_substr($this->description, 0, 500, 'utf-8');
		if (empty($this->name)) {
			$this->setError(400, 40305, 'required parameter is empty'); 
			$this->view = array(
				'error_code' => '杂志名字为空。'
			);
			return FALSE;
		}
		return TRUE;
	}
	
	private function checkName($groupHelper) {
		$result = $groupHelper->checkGroupNameExists($this->name, array('group_id'), $this->groupId);
		if ($result == TRUE) {
			$this->view = array(
				"response" => "Name existed",
				"code" => 0
				);
			return FALSE;
		}
		else {
			$this->view = array(
				"response" => "Name didn't exist",
				"code" => 1
				);
			return TRUE;
		}
	}
}
