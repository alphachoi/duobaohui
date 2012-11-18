<?php
namespace Snake\Package\Group\Helper;

class RedisUserGroupUnFollower extends \Snake\Libs\Redis\Redis {
	static $prefix = "UserGroupUnFollower";

    /**
     * 删除杂志社非粉丝
     * @param $userId 粉丝编号
     * @param $groupIds 杂志社数组
     *
     */
    public static function removeMultiFollowerGroup($userId, $groupIds) {
        foreach($groupIds as $groupId) {
            self::srem($groupId, $userId);
        }
    }

	public static function isUnFollow($groupId, $userId) {
		if (empty($groupId) || empty($userId)) {
			return FALSE;
		}
		return self::sContains($groupId, $userId);
	}

	public static function getUnFollower($groupId) {
		if (empty($groupId)) {
			return FALSE;
		}
		return self::sMembers($groupId);
	}

}
