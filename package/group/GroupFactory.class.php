<?php
namespace Snake\Package\Group;

use \Snake\Package\Group\Helper\DBGroupHelper;
use \Snake\Package\Group\Group;
class GroupFactory {

	protected $groups = array();

	public function __construct($group_ids, $col= "*") {
		$group_id = implode(',', $group_ids);
		$sql = "SELECT $col FROM t_whale_topic_group WHERE group_id IN ({$group_id})";
		$result = DBGroupHelper::getConn()->read($sql, array());
		foreach ($group_ids AS $key => $value) {
			foreach ($result AS $group_info) {
				if ($group_info['group_id'] == $value) {
					$this->groups[$group_info['group_id']] = new Group($group_info);
					break;
				}
			}
		}
	}

	public function getGroups() {
		return $this->groups;
	}

	public function fillElements($start, $limit) {
		$limit = $limit + 3;
		$group_ids = array_keys($this->groups);
		foreach ($this->groups as $group_id => $group) {
			if ($limit > 0) {
				$sql = "SELECT /*groupfac-xj*/twitter_id FROM t_whale_topic_group_twitter WHERE group_id = :_group_id AND have_picture = 1 AND show_type = 0 ORDER BY twitter_id DESC LIMIT :_start, :_limit";
				$sqlData = array('_group_id' => $group_id, '_start' => $start, '_limit' => $limit);
				$result = DBGroupHelper::getConn()->read($sql, $sqlData);
				$twitter_ids = array();
				foreach ($result as $twitter) {
					$twitter_ids[] = $twitter['twitter_id'];
				}
				$twitter_ids = array_reverse($twitter_ids);
				$group->setTwitterIds($twitter_ids);
			}
		}
	}
}
