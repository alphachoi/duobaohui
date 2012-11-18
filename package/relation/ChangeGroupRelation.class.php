<?php
namespace Snake\Package\Relation;

use \Snake\Package\Relation\UserRelationGroup AS UserRelationGroup;
use \Snake\Package\Group\ClearGroupCache AS ClearGroupCache;
use \Snake\Package\Group\UserFollowGroupRedis AS UserFollowGroupRedis;
use \Snake\Package\Msg\UpdateUserNotice AS UpdateUserNotice;
use \Snake\Package\Timeline\Timeline AS Timeline;
use \Snake\Package\ShareOutside\ShareOb AS ShareOb;
use \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnectHelper;


class ChangeGroupRelation implements \Snake\Libs\Interfaces\Iobservable{

	private $observers = array();
	private $userId = NULL;
	private $operationUserId = NULL;
	private $groupId = NULL;
	private $changeRole = NULL;
	
	public function __construct($userId, $operationUserId, $groupId, $role) {
		if (empty($userId) || empty($groupId) || empty($role)) {
			return false;
		}
		$this->userId = $userId;
		$this->operationUserId = $operationUserId;
		$this->groupId = $group_id;
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
		if (empty($this->userId) || empty($this->groupId) || empty($this->role)) {
			return false;
		}
			
	}

}
