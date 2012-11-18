<?php
namespace Snake\Package\Timeline\Helper;

class RedisUserHomePosterTimeline extends \Snake\Libs\Redis\Redis {
	const SIZE = 1800;
	static $prefix = "UserHomePosterTimeline";

    public static function getLastTid($uid) {
        return self::lGet($uid, 0); 
    }   

	public static function getSize($uid) {
		return self::lSize($uid);
	}

	public static function pushTwitter($uid, $tid) {
		if (empty($uid) || empty($tid)) {
			return FALSE;
		}
		self::lPush($uid, $tid);
	}

    public static function pushTwitters($uId, $twitterIds) {
        if (empty($uId) || empty($twitterIds)) { 
			return;
		}
		foreach ($twitterIds AS $twitterId) {
			self::lPush($uId, $twitterId);
		}   
    }  	

	/** 
     * 获取一个用户timeline里的值.
     */
    public static function getTimelineByUid($uid, $offset = 0, $limit = 40) {
		$tids = self::lRange($uid, $offset, $offset + $limit - 1);
		if (!empty($tids)) {
			$tids = array_flip(array_flip($tids));
		}
		return $tids;
    }
    
	/**
	 * 获取一个用户timeline长度
	 */
	public static function getTimelineSizeByUid($uid) {
        return self::lSize($uid);
    } 

	public static function hasId($uid) {
		return self::exists($uid);
	}

    public static function removeTwitters($uid, $twitterIds) {
        foreach ($twitterIds AS $twitterId) {
            self::lRemove($uid, $twitterId);
        }   
    }

	public static function trimTimeline($uid, $length = -1) {
		if (empty($uid)) {
			return FALSE;
		}
		$length = -1 && $length = self::SIZE;
		return self::lTrim($uid, 0, $length - 1);
	}

	public static function trimMultiTimeline($uids, $tid) {
		if (empty($uids) || empty($tid)) {
			return FALSE;
		}
		foreach ($uids as $uid) {
			self::lTrim($uid, 0, self::SIZE - 1);	
		}
	}

	public static function updateMultiTimeline($uids, $tid) {
		if (empty($uids) || empty($tid)) {
			return FALSE;
		}
		foreach ($uids as $uid) {
			if (empty($uid)) {
				continue;
			}
			if (self::exists($uid)) {
				self::lPush($uid, $tid);
			}
		}
	}

	public static function tExists($uid, $tid) {
		if (empty($uid) || empty($tid)) {
			return FALSE;
		}	
		$insert = self::lInsert($uid, 'before', $tid, $tid);
		if ($insert != -1) {
			self::lRemove($uid, $tid, 1);
			return TRUE;
		}
		return FALSE;
	}	

}
