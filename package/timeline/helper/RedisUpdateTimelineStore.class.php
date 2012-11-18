<?php
namespace Snake\Package\Timeline\Helper;

/**
 * zset
 */
class RedisUpdateTimelineStore extends \Snake\Libs\Redis\Redis {
	static $prefix = 'UPDATETIMELINESTORE';
	static $xsync = TRUE;

	public static function InsertUpdateStoreMulti($userId, $groupId, $uids) {
		if (empty($userId) || empty($groupId) || empty($uids)) {
			return FALSE;
		}
		$key = $userId . $groupId;
		$score = time();
		foreach ($uids as $uid) {
			self::zAdd($key, $score, $uid);	
		}
	}

	public static function InsertUpdateStore($userId, $groupId, $uid) {
		if (empty($userId) || empty($groupId)) {
			return FALSE;
		}
		$key = $userId . $groupId;
		$score = time();
		return self::zAdd($key, $score, $uid);	
	}

	public static function DelUpdateStore($userId, $groupId) {
		if (empty($userId) || empty($groupId)) {
			return FALSE;	
		}
		$key = $userId . $groupId;
		return self::delete($key);
	}

	public static function removeMulti($userId, $groupId, $uids) {
		if (empty($userId) || empty($groupId) || empty($uids)) {
			return FALSE;
		}
		$key = $userId . $groupId;
		foreach ($uids as $uid) {
			self::zDelete($key, $uid);
		}
	}

	public static function getUpdateStore($userId, $groupId, $start = 0, $limit = 0, $withscore = FALSE) {
		if (empty($userId) || empty($groupId)) {
			return FALSE;
		}
		$end = $start + $limit - 1;
		$key = $userId . $groupId;
		return self::zRange($key, $start, $end, $withscore);
	}

	public static function getStoreNumber($userId, $groupId) {
		if (empty($userId) || empty($groupId)) {
			return FALSE;
		}
		$key = $userId . $groupId;
		return self::zCard($key);
	}
}
