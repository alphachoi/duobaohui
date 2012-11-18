<?php
namespace Snake\Package\Relation;

use \Snake\Package\Group\Helper\ClearGroupCache AS ClearGroupCache;

class DBUserFollowGroupHelper extends \Snake\Libs\DB\DBModel {
	const _DATABASE_ = 'whale';
}

class UserRelationGroup implements \Snake\Libs\Interfaces\Iobserver{

	private $user_id = NULL;
	private $group_ids = array();
	private $group_id = NULL;
	private $relations = array();

	public function __construct($user_id, $group_ids) {
		if (empty($group_ids) || empty($user_id)) {
			return FALSE;
		}
		$this->user_id = $user_id;
		$this->group_ids = $group_ids;
		$relations = self::check__user_follow_groups();
		foreach ($group_ids as $group_id) {
			$this->relations[$group_id] = isset($relations[$group_id]) ? $relations[$group_id] : NULL;
		}
	}

	public function __get($group_id) {
		return $this->relations[$group_id];
	}

	public function getRelation() {
		return $this->relations;
	}

	private function check__user_follow_groups() {
		$group_ids = implode(',', $this->group_ids);
		$sql = "SELECT role, group_id FROM t_whale_topic_group_user where user_id = :_user_id AND group_id in ({$group_ids})";
		$sqlData['_user_id'] = $this->user_id;
		return DBUserFollowGroupHelper::getConn()->read($sql, $sqlData, FALSE, 'group_id');
	}

	public function create() {
		$role = 1 ;
		$this->setRole($role);
	}

	public function apply() {
		$role = 4 ;
		$this->setRole($role);
	
	}

	public function block() {
		$role = 8 ;
		$this->setRole($role);

	}

	public function follow() {
		$role = 5 ;
		$this->setRole($role);
        foreach ($this->group_ids as $group_id) {     
            if (empty($group_id) || TopicGroupUserModel::getInstance()->isGroupFollower($group_id, $this->user_id)) {
                continue;     
            }    
            /*TopicGroupUserModel::getInstance()->addGroupUser($group_id, $this->user_id, 5);     
            UserGroupFollower::lRemove($this->user_id, $group_id);
            UserGroupFollower::lPush($this->user_id, $group_id);     
            if (UserGroupUnFollower::sContains($group_id, $this->user_id)) {
                UserGroupUnFollower::sRemove($group_id, $this->user_id);     
            }    
            TopicGroupModel::getInstance()->incGroupMemberCount($group_id);     
            Timeline::newGroupFollowing($this->user_id, $group_id);
            $this->addFollowGroupNotice($group_id);
			ClearGroupCache::clearUserGroupCache($this->user_id, $group_id);*/
        }
	}

	public function add() {
		$role = 0 ;
		$this->setRole($role);

	}
	
	private function setRole($role) {
		$group_id = $this->group_ids[0];
		$sql = "REPLACE INTO t_whale_topic_group_user (group_id, user_id, role) value (:_group_id, :_user_id, :_role)";
		$sqlData = array(
			'_group_id' => $group_id,
			'_user_id'	=> $this->user_id,
			'_role'		=> $role
			);

		return DBUserFollowGroupHelper::getConn()->write($sql, $sqlData);
	}

	public function onChanged($sender, $params) {
		print_r("running relation observer!\n");
		$this->setRole($params['role']);
	}
}
