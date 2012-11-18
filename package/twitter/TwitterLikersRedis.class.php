<?php
namespace Snake\Package\Twitter;

Use \Snake\Libs\Redis\Redis AS Redis;
/*
 *Made for fill redis Twitter Likers
 *by zx
 */
class TwitterLikersRedis extends Redis{
	static $prefix = 'TwitterLikers';
	public function addUser($tid, $time, $uids) {
		$prefix = self::getPrefix();
		$key = "{$prefix}:{$tid}";
		foreach ($uids as $uid) {
			self::zAdd($key, $time, $uid);
		}
		return TRUE;
	}

	static function getLikers($tid, $num = 30) {
		$userIds = parent::zRevRange($tid, 0, $num);	
		return $userIds;
	}

}
