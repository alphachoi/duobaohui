<?php
namespace Snake\Package\User;

class DBUserFollowGroupHelper extends \Snake\Libs\DB\DBModel {
	const _DATABASE_ = 'whale';
}

class UserFollowGroup {

	private $user_id = NULL;
	private $group_ids = array();
	private $relations = array();

	public function __construct($user_id, $group_ids) {
		if (empty($group_ids) || empty($user_id)) {
			return FALSE;
		}
		$this->user_id = $user_id;
		$this->group_ids = $group_ids;

		$relations = $this->check_user_follow_groups();
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

	private function check_user_follow_groups() {
		$group_ids = implode(',', $this->group_ids);
		$sql = "SELECT role, group_id FROM t_whale_topic_group_user where user_id = :_user_id AND group_id in ({$group_ids})";
		$sqlData['_user_id'] = $this->user_id;
		return DBUserFollowGroupHelper::getConn()->read($sql, $sqlData, FALSE, 'group_id');
	}

}
