<?php
namespace Snake\Package\Msg;

use \Snake\Package\Msg\Helper\RedisUserNotification AS RedisUserNotification;
Use \Snake\Package\Msg\Helper\RedisUserRepinNotice AS UserRepinNotice;
Use \Snake\Package\Group\Helper\RedisUserGroupFollower AS RedisUserGroupFollower;
use \Snake\Package\Group\Helper\RedisUserGroupUnFollower AS RedisUserGroupUnFollower;
use \Snake\Package\Group\GroupFactory AS GroupFactory;
use \Snake\Package\User\User AS User;
Use \Snake\Libs\Base\CommFun;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\Group\Groups AS Groups;
Use \Snake\Libs\Cache\Memcache  AS Memcache;

class UpdateUserNotice implements \Snake\Libs\Interfaces\Iobserver{

	public function __construct() {

	}
	
	public function onChanged($sender, $params) {
		if (!empty($params['group_id'])) {
			$this->addFollowGroupNotice($params['user_id'], $params['group_id']);
		}
		switch ($sender) {
			case 'Follow' :
				$this->addHomeMessage($params['user_id'], $params['other_id'], $params['userSession']);
				break;
			case 'RegisterFollow' :
				$this->addRegisterMessage($params['user_id'], $params['gid'], $params['group_name'], $params['nickname']);
				break;
			default :
				break;
		}
	}

	public function addFollowGroupNotice($userId, $groupId) {
        if (empty($groupId) || empty($userId)) {
            return ;
        }

        //获取杂志社主编
		//print_r("running UpdateUserNoticeOb!\n");
		$gId = $groupId;
		$groupId = array( 0 => $groupId);
        $groupHandle = new GroupFactory($groupId);
        $groupInfoObj = $groupHandle->getGroups();
        $groupInfo = array();
        $adminIds = array();
		$groupHelper = new GroupUser();
        foreach ($groupInfoObj AS $key) {
            $groupInfo[] = $key->getGroup();
        }
		$user = $groupHelper->getGroupUsersByRole($groupId, array('user_id', 'group_id', 'role', 'created'));
		foreach ($user[$gId] AS $key => $value) {
			$adminIds[] = $key;
		}
		

        $userHelper = new User();
        $userInfo = $userHelper->getUserInfo($userId,  array('nickname', 'avatar_c'));
		if (strpos($userInfo['avatar_c'], 'http://') === FALSE) {
			$userInfo['avatar_c'] = \Snake\Libs\Base\Utilities::convertPicture($userInfo['avatar_c']);
		}
		
        foreach($adminIds as $uid) {
            $params = array(
                'from' => $userId,
                'nickname' => $userInfo['nickname'],
                'type' => 'followgroup',
                'avatar_url' => $userInfo['avatar_c'],
                'time' => time("now"),
                'gid' => $gId,
                'gname' => $groupInfo[0]['name'],
            );
            $oldInfo = UserRepinNotice::getNotice($uid);
            $insert = TRUE;
            foreach($oldInfo as $value) {
                if ($value['type'] == 'followgroup' && $value['from'] == $userId && $value['gid'] == $groupId) {
                    $insert = FALSE;
                    break;
                }
            }
            if ($insert === TRUE) {
                UserRepinNotice::setNotice($uid, $params);
            }
        }		

	}

    /**
     * 添加首页关注人及其全部杂志社消息提示
     * @param $fromId 操作人编号  
     * @param $toId 关注人编号
     *
     */

	public function addHomeMessage($fromId, $toId, $userInfo = array()) {
        if (empty($fromId) || empty($toId)) {
            return FALSE;
        }    

        $params = array(
            'from' => $fromId,
            'nickname' => $userInfo['nickname'],
            'type' => 'follow',
            'avatar_url' => \Snake\Libs\Base\Utilities::convertPicture($userInfo['avatar_c']),
            'time' => time("now"),
        );   

        //检查是否重复

        $oldInfo = UserRepinNotice::getNotice($toId);
        $insert = TRUE;
        foreach($oldInfo as $value) {
            if ($value['type'] == 'follow' && $value['from'] == $userInfo['user_id']) {
                $insert = FALSE;
                break;
            }    
        }    
        if ($insert == TRUE) {
            UserRepinNotice::setNotice($toId, $params); 
        }    
    } 

	/**
	 * 新注册用户首页左侧动态
	 * @param $userId 用户编号
	 * @param $groupId 杂志社编号
	 * @param $groupName 杂志社名称
	 * @param $userInfo 用户信息
	 */
	private function addRegisterMessage($userId, $groupId, $groupName, $nickname) {
		$cacheObj = Memcache::instance();	
		$avatar = $cacheObj->get('users_temp_avatar_' . $userId);
		$avatar = empty($avatar) ? \Snake\Libs\Base\Utilities::convertPicture($userInfo['avatar_c']) : $avatar;
		$paramsOther = array(
            'from' => $userId,
            'nickname' => $nickname,
            'type' => 'registerOther',
            'avatar_url' => $avatar,
            'time' => $_SERVER['REQUEST_TIME'],
        );
	    $params = array(
            'from' => $userId,
            'nickname' => $nickname,
            'type' => 'register',
            'avatar_url' => $avatar, 
            'time' => $_SERVER['REQUEST_TIME'],
            'gid' => $groupId,
            'gname' => $groupName,
        );
		$exists = UserRepinNotice::existNotice($userId);
		if ($exists === FALSE) {
			UserRepinNotice::setNotice($userId, $paramsOther);
			UserRepinNotice::setNotice($userId, $params);
		}
	}

    /*
     * 关注新关注人为主编的杂志task版 
     * @param int $userId 关注人编号
     * @param int $authorId 同$authorId
     *
     * */
	/*
    public function addFollowGroups($userId, $authorId) {

        if (empty($userId) || empty($authorId)) {
            return;
        }
		$groupUserHelper = new GroupUser();
		$result = $groupUserHelper->getUserGroupsByRole(array($userId));

        //$groupIds = DataToArray($result, 'group_id');
		if (!empty($result)) {
			foreach ($result[$userId] AS $key => $value) {
				$groupIds[] = $result[$userId][$key]['group_id'];
			}
		}
        //已关注的所有杂志社
        $orig_group_ids = RedisUserGroupFollower::lRange($authorId, 0, 0);

        //编辑 || 主编 || 屏蔽
		$groupHelper = new Groups();
		$specialKey['key'] = 'role';
		$specialKey['value'] = 5;
		$specialKey['condition'] = 'noteq';
		if (!empty($groupIds)) {
			$editor_admin_apply_result = $groupUserHelper->getGroupRelation($groupIds, $authorId, array('user_id', 'group_id', 'role', 'created'), $specialKey);
			if (!empty($editor_admin_apply_result)) {
				foreach ($editor_admin_apply_result AS $key) {
					if (!empty($editor_admin_apply_result[$key])) {
						foreach ($editor_admin_apply_result[$key] AS $k => $v) {
							$editor_admin_apply_group_ids[] = $editor_admin_apply_result[$key][$k]['group_id'];
						}
					}
				}
			}
		}

        //$editor_admin_apply_result = TopicGroupUserModel::getInstance()->getSpecialGroupUser($groupIds, $authorId);

        //$editor_admin_apply_group_ids = DataToArray($editor_admin_apply_result, 'group_id');

		if (!empty($groupIds) && !empty($orig_group_ids) && !empty($editor_admin_apply_group_ids))  {
		    $groupIds = array_flip(array_flip(array_diff($groupIds, $orig_group_ids, $editor_admin_apply_group_ids)));
		}
		else if (!empty($groupIds) && !empty($orig_group_ids)) {
			$groupIds = array_flip(array_flip(array_diff($groupIds, $orig_group_ids)));
		}
		else if (!empty($groupIds) && !empty($editor_admin_apply_group_ids)) {
			$groupIds = array_flip(array_flip(array_diff($groupIds, $editor_admin_apply_group_ids)));
		}

        //增加杂志社粉丝数
        if (!empty($groupIds)) {
            //TopicGroupModel::getInstance()->decMultiGroupMemberCount($groupIds, 'plus');
			$fields['count_member'] =  'count_member+1';
			$groupHelper->updateGroupInfo($groupIds, $fields);
            foreach($groupIds as $groupId) {
                RedisUserGroupFollower::lRemove($authorId, $groupId);
                RedisUserGroupFollower::lPush($authorId, $groupId);
                if (RedisUserGroupUnFollower::sContains($groupId, $authorId)) {
                    RedisUserGroupUnFollower::sRemove($groupId, $authorId);
                }

            }
        }
    }
	*/

	/**
	 * 取消关注人主编的杂志社
	 * @param $userId 关注人编号
	 * @param $authorId  
	 */
	public function removeFollowGroups($userId, $authorId) {
		if (empty($userId) || empty($authorId)) {
			return FALSE;
		}
		$groupUserHelper = new GroupUser();
		$groupHelper = new Groups();
		$groupIds = array();
		$editorGroup = $groupUserHelper->getUserGroupsByRole(array($userId));
		if (!empty($editorGroup)) {
			foreach ($editorGroup[$userId] AS $key => $value) {
				$groupIds[] = $editorGroup[$userId][$key]['group_id'];
			}
			//关注的杂志社和主编杂志社取交集
			$followGroup = RedisUserGroupFollower::getFollowGroups($authorId);	
			$decGroup = array_intersect($groupIds, $followGroup);
			//从Redis中去除
			RedisUserGroupFollower::removeMultiGroupFollower($authorId, $decGroup);
			RedisUserGroupUnFollower::removeMultiFollowerGroup($authorId, $decGroup);

			//批量减少杂志社的关注数
			$fields['count_member'] =  'count_member-1';
			$groupHelper->updateGroupInfo($decGroup, $fields);

			//移除数据库中权限为申请加入和关注状态的记录
			$groupUserHelper->delUserGroupsByRole($authorId, $groupIds, array(4, 5));
		}
	}
}
