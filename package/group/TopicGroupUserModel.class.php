<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper AS DBGroupHelper;
Use \Snake\Libs\Cache\Memcache;

class TopicGroupTwitterModel{
	

	public function __construct() {
	}
	/**
	 根据一组tids得到一组group_ids
	 */
	public function getGroupIdsByTids($tids, $col = "*") {
		if (empty($tids)) {
			return array();
		}
		$twitter_info = array();
		$cache = Memcache::instance();
		$group_ids = array();
		//标志是不是全部cache住了。
		$i = 0;
		foreach ($tids AS $key => $value) {
			$cache_key = "TOPIC_GROUP_TWITTER_RELATION_" . $value;
			$twitter_info[$value] = $cache->get($cache_key);
			if (!empty($twitter_info[$value]['group_id'])) {
				$group_ids[$value] = $twitter_info[$value]['group_id'];
				unset($tids[$key]);
			}
			else {
				$i++;
			}
		}
		if ($i === 0) {
			return $group_ids;	
		}
		$tidStr = implode(',', $tids);
		$sql = "SELECT $col FROM t_whale_topic_group_twitter WHERE twitter_id IN ($tidStr) AND show_type = 0";
		$result = array();
		$result = DBGroupHelper::getConn()->read($sql, $sqlData, FALSE, 'twitter_id');
		foreach ($result AS $tid => $group_info) {
			$group_ids[$tid] = $group_info['group_id'];
		}
		return $group_ids;
	}

    
	
}
