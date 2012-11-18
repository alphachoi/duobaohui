<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper AS DBGroupHelper;
Use \Snake\Libs\Cache\Memcache;

class TopicGroupModel{
	

	public function __construct() {
	}
	/**
	 * 根据一组group_ids得到一组group_names
	 */
	public function getGroupNamesByGroupIds($group_ids, $col = "*") {
		foreach ($group_ids AS $key => $value) {
			if (empty($value)) {
				unset($group_ids[$key]);
			}
		}
		if (empty($group_ids)) {
			return array();
		}
		if (is_array($group_ids)) {
			$cache_result = array();
			foreach ($group_ids AS $group_id) {
				$cache = Memcache::instance();
				$cache_key = "TOPIC_GROUP_" . $group_id;
				$result = $cache->get($cache_key);
				if (!empty($result)) {
					$cache_result[$group_id] = $result;
				}
			}
		}
		$groupIdStr = implode(',', $group_ids);
		$sql = "SELECT $col FROM t_whale_topic_group WHERE group_id in ($groupIdStr)";
		$result = array();
		$result = DBGroupHelper::getConn()->read($sql, $sqlData, FALSE, "group_id");
		$group_names = array();
		if (!empty($cache_result)) {
			$result = $cache_result + $result;
		}
		foreach ($result AS $group_id => $group_info) {
			$group_names[$group_id] = $group_info['name'];
		}
		return $group_names;
	}

	
}
