<?php
namespace Snake\Package\Group\Helper;

class RedisUserGroupFollower extends \Snake\Libs\Redis\Redis {
	static $prefix = "UserGroupFollower";
 	
    /** 
     * 更新批量用户关注杂志社
     * @param $userIds 粉丝数组
     * @param $groupId 杂志社编号
     * @param $admin_id 过一阵可去掉
     * Note:冷数据不更新($userIds和timeline同步)
     */
    public static function pushMultiUserGroupFollower($userIds, $groupId, $admin_id = 0) {
        foreach($userIds as $userId) {
            if (empty($userId) || $userId == 219) {
                continue;
            }
            self::lPush($userId, $groupId);
        }
    }

    /** 
     * 批量更新用户关注杂志社
     * @param $userId   粉丝编号
     * @param $groupIds 杂志社编号数组
     *
     */
    public static function pushMultiGroupUserFollower($userId, $groupIds) {
		if (is_array($groupIds)) {
			foreach($groupIds as $groupId) {
				if (empty($groupId)) {
					continue;
				}
				self::lRemove($userId, $groupId);
				self::lPush($userId, $groupId);
			}
		}
    }

    /**
     * 批量移除用户关注的杂志社 
     * @param $userId 用户编号
     * @param $groupIds 杂志社编号数组
     *
     */
    public static function removeMultiGroupFollower($userId, $groupIds) {
        foreach($groupIds as $groupId) {
            $result = self::lRemove($userId, $groupId);
			$log = new \Snake\Libs\Base\SnakeLog('removemulti', 'normal');
			$log->w_log(print_r(array($userId, $groupId, $result), true));
        }
    }

    /**
     * 批量移除用户的杂志社 
     *
     *
     */
    public static function removeMultiFollowGroups($userIds, $groupId) {
        foreach($userIds as $userId) {
            self::lRemove($userId, $groupId);
        }
    }

	/**
	 * 获取一个人关注的杂志社
	 * @param $userId 用户编号
	 *
	 */
	public static function getFollowGroups($userId, $offset = 0, $limit = 0) {
		if (empty($userId)) {
			return FALSE;
		}
		return self::lRange($userId, $offset, $offset + $limit - 1);	
	}

	/**
	 * 获取关注杂志社总数
	 * @param $userId 用户编号
	 */
	public static function getFollowGroupCount($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		return parent::lSize($userId);
	}

	/**
	 * 不要使用,请使用getFollowGroupCount
	 */
	public static function lSize($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		return parent::lSize($userId);
	}
}
