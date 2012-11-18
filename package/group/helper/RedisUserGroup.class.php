<?php
namespace Snake\Package\Group\Helper;

/**
 * 用户关注杂志社集合(zset)
 */
class RedisUserGroup extends \Snake\Libs\Redis\Redis {
	static $prefix = "RedisUserGroup";
 	
    /** 
     * 更新批量用户关注杂志社
     * @param $userIds array 粉丝数组
     * @param $groupId integer 杂志社编号
	 * @param $time string 关注时间
     * Note:冷数据不更新($userIds和timeline同步)
     */
    public static function addUserGroupFollower($userIds, $groupId, $time = 0) {
		if (empty($userIds) || empty($groupId)) {
			return FALSE;
		}
		empty($time) && $time = time();
		!is_array($userIds) && $userIds = array($userIds);
        foreach($userIds as $userId) {
			if (empty($userId)) {
				continue;
			}
            self::zAdd($userId, $time, $groupId);
        }
    }

    /** 
     * 批量更新用户关注杂志社
     * @param $userId   integer 用户编号
     * @param $groupIds array 杂志社编号数组
     * @param $time string 关注时间
     */
    public static function addGroupUserFollower($userId, $groupIds, $time = 0) {
		if (empty($userId) || empty($groupIds)) {
			return FALSE;
		}
		$time == 0 && $time = time();
		!is_array($groupIds) && $groupIds = array($groupIds);
		foreach($groupIds as $groupId) {
			if (empty($groupId)) {
				continue;
			}
			self::zAdd($userId, $time, $groupId);
		}
    }

	/**
	 * 批量关注杂志社
	 * @param $userId integer 用户编号
	 * @param $groupInfos array 杂志社详细信息
	 *
	 */
	public static function addGroups($userId, $groupInfos) {
		if (empty($userId) || empty($groupInfos)) {
			return FALSE;
		}	
		foreach ($groupInfos as $info) {
			$gid = $info['group_id'];
			$time = $info['created'];
			if (empty($gid)) {
				continue;
			}
			self::zAdd($userId, $time, $gid);
		}	
	}


    /**
     * 批量移除用户关注的杂志社 
     * @param $userId 用户编号
     * @param $groupIds 杂志社编号数组
     *
     */
    public static function removeMultiGroupFollower($userId, $groupIds) {
		if (empty($userId) || empty($groupIds)) {
			return FALSE;
		}
		!is_array($groupIds) && $groupIds = array($groupIds);
        foreach($groupIds as $groupId) {
            self::zDelete($userId, $groupId);
        }
    }

    /**
     * 批量移除用户的杂志社 
	 * @param $userIds array 用户编号
	 * @param $groupId integer 杂志社编号
     *
     */
    public static function removeMultiFollowGroups($userIds, $groupId) {
		if (empty($userIds) || empty($groupId)) {
			return FALSE;
		}
		!is_array($userIds) && $userIds = array($userIds);
        foreach($userIds as $userId) {
            self::zDelete($userId, $groupId);
        }
    }

	/**
	 * 获取一个人关注的杂志社
	 * @param $userId 用户编号
	 * @param $start 开始索引
	 * @param $limit 查询数量 0表示从start至最后
	 * @param $order 排序
	 * @param $withscore 是否返回排序score，若为true,数组键值是交替的
	 *
	 */
	public static function getFollowGroups($userId, $start = 0, $limit = 0, $order = 'DESC', $withscore = FALSE) {
		if (empty($userId)) {
			return FALSE;
		}
		$end = $start + $limit - 1;
		if ($order == 'DESC') {
			return self::zRevRange($userId, $start, $end, $withscore);
		}
		else {
			return self::zRange($userId, $start, $end, $withscore);
		}
	}

	/**
	 * 返回用户的关注杂志社数量
	 * @param $userId integer 用户编号
	 */
	public static function getFollowGroupCount($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		return self::zSize($userId);
	}

	/**
	 * 判断是否关注了该杂志社
	 * @param $userId 用户编号
	 * @param $groupId 杂志社编号
	 */
	public static function isGroupFollowed($userId, $groupId) {
		if (empty($userId) || empty($groupId)) {
			return FALSE;
		}
		$score = self::zScore($userId, $groupId);
		return $score !== FALSE;
	}
}
