<?php
namespace Snake\Package\Group\Helper;

class UserGroupFollower extends \Snake\Libs\Redis\Redis {
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
            if ($userId == 0) {
                UserGroupFollower::lRemove($admin_id, 0);   
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
        foreach($groupIds as $groupId) {
            self::lPush($userId, $groupId);
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
            self::lRemove($userId, $groupId);
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


}
