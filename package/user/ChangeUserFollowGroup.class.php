<?php
namespace Snake\Package\User;

USE \Snake\Package\Group\Groups AS Groups;
USE \Snake\Package\Group\GroupUser AS GroupUser;
USE \Snake\Package\Group\Helper\RedisUserGroupUnFollower AS RedisUserGroupUnFollower;
USE \Snake\Package\Group\Helper\RedisUserGroupFollower AS RedisUserGroupFollower; 

class ChangeUserFollowGroup implements \Snake\Libs\Interfaces\Iobserver {
	
	public function __construct() {

	}

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Follow' :
				$this->addFollowGroups($args['user_id'], $args['other_id']);
				break;
			case 'CancelFollow' :
				$this->removeFollowGroups($args['user_id'], $args['other_id']);
				break;
			case 'RegisterFollow' :
				$this->addMultiGroups($args['user_id'], $args['group_ids']);
				break;
			default :
				break;
		}
	}

	private function addFollowGroups($userId, $otherId) {
		if (empty($userId) || empty($otherId)) {
			return FALSE;
		}
		$groupUserHelper = new GroupUser();

		//关注人主编的杂志社
		$groupIds = array();
		$result = $groupUserHelper->getUserGroupsByRole(array($otherId));
		if (empty($result)) {
			return; 
		}
		foreach ($result[$otherId] AS $key => $value) {
			$groupIds[] = $result[$otherId][$key]['group_id'];
		}

		//个人 主编||编辑||封禁||关注的杂志社(t_whale_topic_group_user)
		$ownGroups = $groupUserHelper->getUserGroupsByRole(array($userId), array(0, 1, 5, 8), array('group_id'));
		$ownGroups = \Snake\Libs\Base\Utilities::DataToArray($ownGroups, 'group_id');
		$pushArray = $this->isInArray($groupIds, $ownGroups);
		unset($ownGroups);

		//增加杂志社粉丝数
        if (!empty($pushArray)) {
            $fields['count_member'] =  'count_member+1';
			$fields['add_himself'] = 1;
			$pushArray = array_values($pushArray);
			$groupHelper = new Groups();
            $groupHelper->updateGroupInfo($pushArray, $fields);
			RedisUserGroupFollower::pushMultiGroupUserFollower($userId, $pushArray);
            foreach($pushArray as $groupId) {
                if (RedisUserGroupUnFollower::sContains($groupId, $userId)) {
                    RedisUserGroupUnFollower::sRemove($groupId, $userId);
                }
            }
        }
	}

	/**
	 * 取消关注人取消他主编的杂志社
	 */	
	private function removeFollowGroups($userId, $otherId) {
		if (empty($userId) || empty($otherId)) {
            return FALSE;
        }
        $groupUserHelper = new GroupUser();
        $groupHelper = new Groups();
        $groupIds = array();
		//关注人主编杂志社
        $editorGroup = $groupUserHelper->getUserGroupsByRole(array($otherId));
        if (!empty($editorGroup)) {
            foreach ($editorGroup[$otherId] AS $key => $value) {
                $groupIds[] = $editorGroup[$otherId][$key]['group_id'];
            }

            //关注的杂志社和主编杂志社取交集,分段取
			$followGroupSize = RedisUserGroupFollower::getFollowGroupCount($userId);
			$limit = 10000;
			$decGroup = array();
			$times = ceil($followGroupSize / $limit);
			for ($i=0; $i<$times; $i++) {
				$subGroups = array();
				$subGroups = RedisUserGroupFollower::getFollowGroups($userId, $i * $limit, $limit);
				$removeGroups = $this->isInArray($groupIds, $subGroups, TRUE);
				$decGroup = array_merge($decGroup, $removeGroups);	
			}

			RedisUserGroupFollower::removeMultiGroupFollower($userId, $decGroup);
            RedisUserGroupUnFollower::removeMultiFollowerGroup($userId, $decGroup);

            //批量减少杂志社的关注数
            $fields['count_member'] =  'count_member-1';
			$fields['add_himself'] = 1;
            $groupHelper->updateGroupInfo($decGroup, $fields);

            //移除数据库中权限为申请加入和关注状态的记录 
            $groupUserHelper->delUserGroupsByRole($userId, $decGroup, array(4, 5));
        }
	}

	/**
	 * 用户批量关注杂志社
	 * @param $userId integer 用户编号
	 * @param $groupIds array 杂志社编号数组
	 *
	 */
	private function addMultiGroups($userId, $groupIds) {
		if (empty($userId) || empty($groupIds)) {
			return FALSE;
		}
		$groupUserHelper = new GroupUser();	
		$userGroupInfos = array();
		foreach ($groupIds as $k => $gid) {
			if (empty($gid)) {
				unset($groupIds[$k]);
				continue;
			}
			else {
				$userGroupInfos[] = array('user_id' => $userId, 'group_id' => $gid, 'role' => 5);				
			}
		}
		//写入到DB
		$groupUserHelper->insertGroupUserMultiple($userGroupInfos);
		//写入到Redis
		RedisUserGroupFollower::pushMultiGroupUserFollower($userId, $groupIds);
	}

	/**
	 * $keys 数组较少。比array_diff 高效(待测试)
	 *
	 */
	private function isInArray($keys, $array, $type = FALSE) {
		$pushArray = array();
		$array = array_flip($array);
		foreach ($keys as $gid) {
			if (array_key_exists($gid, $array) === $type) {
				$pushArray[] = $gid;
			}	
		}
		return $pushArray;
	}
}
