<?php
namespace Snake\Package\Group;

use \Snake\Package\Relation\UserRelationGroup AS UserRelationGroup;
use \Snake\Package\Group\ClearGroupCache AS ClearGroupCache;
use \Snake\Package\Group\UserFollowGroupRedis AS UserFollowGroupRedis;
use \Snake\Package\Msg\UpdateUserNotice AS UpdateUserNotice;
use \Snake\Package\Timeline\Timeline AS Timeline;
use \Snake\Package\ShareOutside\ShareOb AS ShareOb;
use \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnectHelper;
use \Snake\Package\Group\GroupUser AS GroupUser;

class ChangeGroupRelation implements \Snake\Libs\Interfaces\Iobservable{

	private $observers = array();
	private $userId = NULL;
	private $groupId = NULL;
	private $operationUserId = NULL;
	private $changeRole = NULL;
	
	public function __construct($userId, $operationUserId, $groupId, $role) {
		if (empty($userId) || empty($groupId) || empty($role)) {
			return false;
		}
		$this->userId = $userId;
		$this->operationUserId = $operationUserId;
		$this->groupId = $groupId;
		$this->changeRole = $role;

	}

    public function addObserver($observer) {
        $this->observers[] = $observer;
    }   

    public function runObserver() {
		$sender = "ChangeGroupRelation";
		$params = array(
			'user_id' => $this->userId,
			'group_id' => $this->groupId,
			'role'	=> $this->changeRole
			);
        foreach ($this->observers AS $observer) {
            $observer->onChanged($sender, $params);
        }
    }
	
	public function runBeta() {
		if (empty($this->userId) || empty($this->groupId) || empty($this->changeRole)) {
			return false;
		}
		$relationHelper = new UserRelationGroup($this->userId, array(0 => $this->groupId));
		//$userReltion = $relationHelper->getRelation();
		$groupUserHelper = new GroupUser();
		//if ($this->userId == $this->operationUserId && empty($userReltion[$this->groupId]['role'])) {
		if ($this->changeRole == 5) {
			$isFollower = $groupUserHelper->isGroupFollower($this->groupId, $this->userId);
			if ($isFollower == FALSE) {
				$this->runFollow($relationHelper);
			}
		}
		else {
			$userRelation = $groupUserHelper->getGroupRelation($this->groupId, $this->userId);
			if (in_array($this->operationUserId, $this->superAdmins) || $userRelation[$this->operationUserId]['role'] == 1) {
				$this->runChange();//TBC
			}
		}
		$this->runObserver();
	}

	private function runFollow($relationHelper) {
		$this->addObserver($relationHelper);
		$this->addObserver(new ClearGroupCache());
		$this->addObserver(new UserFollowGroupRedis());
		$this->addObserver(new UpdateUserNotice());
		$this->addObserver(new Timeline());
		$settings = RedisUserConnectHelper::getUserSetting('qplus', $this->userId);
		$result = json_decode($settings, TRUE);
		if ( $result['sync_answer'] == 1 ) {
			$flag = 'follow';
			$this->addObserver(new ShareOb());
		//  $this->_qplusSync($flag, $this->group['group_id']);
		}
	}

	private function runChange() {
		print_R("so nice~!");
	}


}
