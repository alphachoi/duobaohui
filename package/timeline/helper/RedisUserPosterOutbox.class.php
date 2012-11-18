<?php

namespace Snake\Package\Timeline\Helper;


class RedisUserPosterOutbox extends \Snake\Libs\Redis\Redis {
	
	protected static $prefix = 'UserPosterOutbox';
	const SIZE = 300;
	
	public static function pushTwitter($uid, $tid) {
		if (empty($uid) || empty($tid)) {
			return FALSE;
		}
		self::lPush($uid, $tid);
	}

	public static function trimTwitter($uid) {
		if (empty($uid)) {
			return FALSE;
		}
		self::lTrim($uid, 0, self::SIZE - 1);
	}

	public static function getTwitter($uid, $offset = 0, $limit = 50) {
		if (empty($uid)) {
			return FALSE;
		}
		$end = $offset + $limit - 1;
		return self::lRange($uid, $offset, $end);
	}
}
