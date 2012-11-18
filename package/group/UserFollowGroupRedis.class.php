<?php
namespace Snake\Package\Group;

use \Snake\Package\Group\Helper\RedisUserGroupFollower AS UserGroupFollower;
use \Snake\Package\Group\Helper\RedisUserGroupUnFollower AS UserGroupUnFollower;


class UserFollowGroupRedis implements \Snake\Libs\Interfaces\Iobserver{

    public function __construct() {

    }   

	public function onChanged($sender, $params) {//$userId, $groupId, $role) {
		$this->updateRedisForFollow($params['user_id'], $params['group_id']);
    }

	private function updateRedisForFollow($userId, $groupId){
		//print_r("running UserGroupUnFollowerOb!\n");
		UserGroupFollower::lRemove($userId, $groupId);
		UserGroupFollower::lPush($userId, $groupId);

		if (UserGroupUnFollower::sContains($groupId, $userId)) {
			UserGroupUnFollower::sRemove($groupId, $userId);
		}    
		/*TopicGroupModel::getInstance()->incGroupMemberCount($groupId);
		//给杂志社主编发Notice
		$this->addFollowGroupNotice($groupId);*/
	}
}
