<?php
namespace Snake\Package\Timeline\Helper;

/**
 * 用户关注的杂志社
 * type zSet
 * key UserGroup:$user_id
 */
class RedisUserGroup extends \Snake\Libs\Redis\Redis {
	
	static $prefix = 'UserGroup';

	public static function getFollowGroups($userId, $start = 0, $end = -1, $options = array()) {
		if (empty($userId)) {
			return FALSE;
		}
		$end == -1 && $end = time();
		return self::zRevRangeByScore($userId, $end, $start, $options);
	}

	public static function addGroup($userId, $gId, $score = -1) {
		if (empty($userId) || empty($gId) || empty($score)) {
			return FALSE;
		}
		$score == -1 && $score = time();
		return self::zAdd($userId, $score, $gId);
	}

	public static function addGroups($userId, $gInfo) {
		if (empty($userId) || empty($gInfo)) {
			return FALSE;
		}
		foreach ($gInfo as $info) {
			if (!empty($info)) {
				$gid = $info['gid'];
				$gtime = $info['time'];
				self::zAdd($userId, $gtime, $gid);
			}
		}
	}
}
