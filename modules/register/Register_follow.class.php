<?php
namespace Snake\Modules\Register;

USE \Snake\Package\Group\Groups AS Groups;
USE \Snake\Package\User\User AS User;
USE \Snake\Libs\Base\Utilities AS Utilities;

/**
 * 注册用户关注杂志社
 * 选择分类后触发
 */
class Register_follow extends \Snake\Libs\Controller {
	
	private $userId = NULL;
	private $typeName = '复古文艺';
	private $groupInfo = array();

	const GROUPNUM = 8;
	const PAGETYPE = 99;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$this->setGroups();
		$this->view = array(
			'gInfo' => $this->groupInfo,
		);	

		return TRUE;
	}

	private function _init() {
		if (!$this->setUserId()) {
			return FALSE;
		}
		$this->setTypeName();
		return TRUE;
	}

	private function setUserId() {
        if (empty($this->userSession['user_id'])) {
            $this->setError(400, 40201, 'Please login first');
            return FALSE;
        }
        if (intval($this->userSession['level']) === 5) {
            $this->setError(400, 40205, 'This account is blocked by anti-spam, user_id: ' . $this->userId);
            return FALSE;
        }
        $this->userId = $this->userSession['user_id'];
        return TRUE;
    }

	private function setTypeName() {
		if (!empty($this->request->REQUEST['type_name'])) {
			$this->typeName = trim($this->request->REQUEST['type_name']);
		}	
		return TRUE;
	}

	/**
	 * FOR HUAZHU
	 * 根据分类获取杂志社
	 */
	private function setGroups() {
		$group = new Groups();	
		$user = new User();
		$groupIds = $group->getKindsOfGroups($this->typeName, self::PAGETYPE, self::GROUPNUM);	
		/*测试数据*/
		/*
		$groupIds = array(
			14090261,
			14090259,
			14090252,
			14090251,
		);*/
		$groupHelper = new Groups();
		$groupInfo = $groupHelper->getGroupSquareInfo($groupIds, $this->userId);
		$adminUids = array();
		foreach ($groupInfo as $gInfo) {
			$adminUids[] = $gInfo['admin_uid'];	
		}
		$adminInfo = $user->getUserInfos($adminUids);
		foreach ($groupInfo as $key => $fgInfo) {
			$uid = $fgInfo['admin_uid'];
			$name = $adminInfo[$uid]['nickname'];
			$avatar = $adminInfo[$uid]['avatar_c'];
			$groupInfo[$key]['admin_name'] = $name;
			$groupInfo[$key]['admin_avatar'] = $avatar;
			unset($groupInfo[$key]['header_path']);
			unset($groupInfo[$key]['count_member']);
			unset($groupInfo[$key]['num']);
			unset($groupInfo[$key]['admin_uid']);
		}
		$this->groupInfo = array_values($groupInfo);
	}
}
