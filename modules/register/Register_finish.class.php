<?php
namespace Snake\Modules\Register;

USE \Snake\Package\Session\UserSession AS UserSession;
USE \Snake\Package\User\ChangeUserFollowGroup AS ChangeUserFollowGroup;
USE \Snake\Package\Timeline\Timeline AS Timeline;
USE \Snake\Package\Group\Helper\RedisUserGroup;
USE \Snake\Package\Group\GroupUser AS GroupUser;
USE \Snake\Libs\Base\Utilities AS Utilities;
USE \Snake\Package\Msg\UpdateUserNotice AS UpdateUserNotice;

/**
 * 关注杂志社
 * 进海报墙等操作
 */
class Register_finish extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {

	private $groupIds = array();
	private $groupName = array();
	private $userId = NULL;
	private $url = 'ihome';
	private $observers = array();
	
	public function run() { 
		if (!$this->_init()) {
			return FALSE;	
		}

		$this->addObserver(new ChangeUserFollowGroup());
		$this->addObserver(new Timeline());
		$this->addObserver(new UpdateUserNotice());

        foreach ($this->observers as $obs) {
            $obs->onChanged('RegisterFollow', array(
				'user_id' => $this->userId,
				'group_ids' => $this->groupIds,
				'gid' => $this->groupIds[0],
				'group_name' => $this->groupName,
				'nickname' => $this->userSession['nickname'],
            ));
        }

		$this->view = array(
			'url' => $this->url,
		);
	}

	private function _init() {
		if (!$this->setUserId()) {
			return FALSE;
		}
		if (!$this->setGroupIds()) {
			return FALSE;
		}
		if (!$this->setGroupName()) {
			return FALSE;
		}
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
	
	private function setGroupIds() {
		if (empty($this->request->REQUEST['group_ids'])) {
			$this->setError(400, 40150, 'empty group_ids');	
			return FALSE;
		}
		$groupIds = $this->request->REQUEST['group_ids'];
		$groupIds = explode(',', $groupIds);
		/*
		//主编&编辑杂志社
		$groupUserHelper = new GroupUser();	
        $ownGroups = $groupUserHelper->getUserGroupsByRole(array($this->userId), array(0, 1), array('group_id'));
        $ownGroups = Utilities::DataToArray($ownGroups, 'group_id');
		foreach ($groupIds as $key => $gid) {
			if (RedisUserGroup::isGroupFollowed($this->userId, $gid) === TRUE || in_array($gid, $ownGroups)) {
				unset($groupIds[$key]);
			}
		}
		 */
		$this->groupIds = $groupIds;
		return TRUE;
	}

	private function setGroupName() {
		if (empty($this->request->REQUEST['group_name'])) {
			$this->setError(400, 40151, 'empty group_name');
			return FALSE;
		}	
		$groupName = explode(',', $this->request->REQUEST['group_name']);
		$this->groupName = array_shift($groupName);
		return TRUE;
	}

	public function addObserver($observer) {
		$this->observers[] = $observer;	
	}
}
