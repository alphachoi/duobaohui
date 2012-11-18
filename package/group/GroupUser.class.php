<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;
Use \Snake\Package\Group\GroupTwitterPersistenceFactory;
Use \Snake\Package\Group\GroupFactory;
Use \Snake\Package\Group\GroupCache;
Use \Snake\Package\Group\Helper\RedisUserGroupFollower;
Use \Snake\Package\Group\Helper\RedisUserGroupUnFollower;
Use \Snake\Package\Group\Helper\RedisGroupInvitation;
Use \Snake\Package\User\User;
Use \Snake\Package\User\Helper\RedisUserFollow;
Use \Redis AS Redis;
Use \Snake\Package\User\Helper\RedisUserFans;
Use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline;
use \Snake\Package\Relation\UserRelationGroup AS UserRelationGroup;
use \Snake\Package\Group\ChangeGroupRelation AS ChangeGroupRelation;
use \Snake\Package\Group\ClearGroupCache AS ClearGroupCache;
use \Snake\Package\Group\UserFollowGroupRedis AS UserFollowGroupRedis;
use \Snake\Package\Msg\UpdateUserNotice AS UpdateUserNotice;
use \Snake\Package\Timeline\Timeline AS Timeline;
use \Snake\Package\ShareOutside\ShareOb AS ShareOb;
use \Snake\Package\msg\Msg;
use \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnectHelper;


class GroupUser {
	
	private $groupUsers = array();
	private $superUsers = array(219,1751,1431119,1765,1698845,1590448,1714106,3896618, 7579460,7222759, 6140112, 10918214); 
	private $otherGroups = array(2, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 18, 13354871, 13232492, 15100011, 16274008, 16274019, 16274026, 16274047);
	private $enterGroups = array(12028978, 14898409, 16274008, 16274019, 16274026, 16274047);
	private $table = 't_whale_topic_group_user';	

	public function __construct() {
	
	}

	public function getGroupUsersByRole($groupIds = array(), $fields = array('user_id', 'group_id', 'role', 'created'), $role = 1, $start = 0, $limit = 20, $orderBy = "created DESC") {
		if (empty($groupIds)) {
			return FALSE;
		}
		
		foreach ($groupIds AS $groupId) {
			$identityObject = new IdentityObject();
			//$identityObject->field('twitter_id')->in($twitterIds);
			if (!is_array($role)) {
				$identityObject->field('group_id')->eq($groupId)->field('role')->eq($role)->orderby($orderBy)->limit($start . ', ' . $limit);
			}
			else {
				$identityObject->field('group_id')->eq($groupId)->field('role')->in($role)->orderby($orderBy)->limit($start . ', ' . $limit);
			}
			$identityObject->col($fields);
			$domainObjectAssembler = new DomainObjectAssembler(GroupUserPersistenceFactory::getFactory('\Snake\Package\Group\GroupUserPersistenceFactory'));
			$groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
			$user = array();
			while ($groupCollection->valid()) {
				$groupObj = $groupCollection->next();   
				$user[$groupObj->getGroupUserId()] = $groupObj->getGroupUser();
			}
			$this->groupUsers[$groupId] = $user;
		}

		return $this->groupUsers;

	}

	public function getUserGroupsByRole($userIds = array(), $role = array(1), $fields = array('user_id', 'group_id', 'role', 'created'), $orderBy = "user_id DESC", $start = 0, $limit = 30000, $extCondition = array()) {

		if (empty($userIds)) {
			return FALSE;
		}

		$Params = array();
		$paramsWhere = array(
			array(
				'operation' => 'in',
				'key' => 'user_id',
				'value' => $userIds
				),
			array(
				'operation' => 'in',
				'key' => 'role',
				'value' => $role
				),
			);
		$paramsEx = array(
			'orderby' => $orderBy
			);
		if (!empty($limit)) {
			$paramsEx['limit'] = $start . ', ' . $limit;
		}
		$parameters = array(
			'where' => $paramsWhere,
			'ext_where' => $extCondition,
			'ext_condition' => $paramsEx,
			'fields' => $fields
		);
		$hashKey = "user_id";	
		if (!in_array($hashKey, $fields)) {
			$hashKey = "";
		}
		$result = OperationDB::selectDataBase($parameters, 'GroupUser', $hashKey);
		$groupUsers = $result;
		return $groupUsers;
	}

	public function getGroupRelation($groupIds = array(), $userId, $fields = array('user_id', 'group_id', 'role', 'created'), $specialKey = array()) {
		if (empty($groupIds) || empty($userId)) {
			return FALSE;
		}

		$groupCacheHelper = new GroupCache();
		$userInfo = $groupCacheHelper->getGroupAdminCache($groupIds);
		if (!empty($userInfo) && $userInfo['user_id'] == $userId) {
			if (!empty($specialKey['group_id'])) {
				$user[$userId][$userInfo['group_id']] = $userInfo;
			}
			else {
				$user[$userId][] = $userInfo;
			}
			return $user;
		}

		$identityObject = new IdentityObject();

		//$identityObject->field('twitter_id')->in($twitterIds);
		if (!is_array($groupIds)) {
			$identityObject->field('group_id')->eq($groupIds)->field('user_id')->eq($userId);
		}
		else {
			$identityObject->field('group_id')->in($groupIds)->field('user_id')->eq($userId);
		}
		if (!empty($specialKey['key'])) {
			$identityObject->field($specialKey['key'])->$specialKey['condition']($specialKey['value']);
		}
		$identityObject->col($fields);
		$domainObjectAssembler = new DomainObjectAssembler(GroupUserPersistenceFactory::getFactory('\Snake\Package\Group\GroupUserPersistenceFactory'));
		$groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$users = array();
		while ($groupCollection->valid()) {
			$groupObj = $groupCollection->next();   
			$userId = $groupObj->getGroupUserId();
			$userInfo = $groupObj->getGroupUser();
			if (!empty($userId)) {
				if (!empty($specialKey['group_id'])) {
					$user[$userId][$userInfo['group_id']] = $userInfo;
				}
				else {
					$user[$userId][] = $userInfo;
				}
			}
			else {
				$user[] = $userInfo;
			}
		}
		$groupUsers = $user;
		return $groupUsers;

	}
	
	public function isGroupFollower($groupId, $userId, $dbCheck = TRUE) {
		if (empty($groupId) || empty($userId)) {
			return FALSE;
		}
		
		if (RedisUserGroupFollower::exists($userId) == FALSE) {
			return FALSE;
		}

		if ($userId == 219) {
			return FALSE;
		}
		//$groupId = 10557;$userId = 1698845;
        $insert = RedisUserGroupFollower::lInsert($userId, Redis::BEFORE, $groupId, $groupId); 
        if ($insert != -1 && !empty($insert)) {
            RedisUserGroupFollower::lRemove($userId, $groupId, 1);
            return TRUE;
        }    
        else {
			if (empty($dbCheck)) {
				return FALSE;
			}
			//echo "died";
            $user = $this->getGroupRelation($groupId, $userId);
            if ($user[$userId][0]['role'] != 8 && !empty($user[$userId])) {
                return TRUE;
            }    
            else {
                return FALSE;
            }    
        }

	}
	
	/**
	 * 判断是否关注杂志，多杂志版
	 * @author Chen Hailong
	 * @params $groupIds array
	 * @params $userId, int
	 */
	public function isGroupFollowerMulti($groupIds, $userId) {
		if (empty($groupIds) || empty($userId)) {
			return FALSE;
		}
		$follow = array();	
		if (RedisUserGroupFollower::exists($userId) == FALSE || $userId == 219) {
			foreach ($groupIds as $groupId) {
				$follow[$groupId] = 0;	
			}
			return $follow;
		}
		$dbGids = array();
		$flag = FALSE;
		foreach ($groupIds as $groupId) {
			$insert = RedisUserGroupFollower::lInsert($userId, Redis::BEFORE, $groupId, $groupId); 
			if ($insert != -1 && !empty($insert)) {
				RedisUserGroupFollower::lRemove($userId, $groupId, 1);
				$follow[$groupId] = 1;
			}    
			else {
				$flag = TRUE;
				$dbGids[] = $groupId;
			}
		}
		if (!$flag) {
			return $follow;
		}
		$relations = $this->getGroupRelation($dbGids, $userId, array('user_id', 'group_id', 'role', 'created'), array('group_id' => 1));

		foreach ($dbGids as $groupId) {
			if (!empty($relations[$userId]) && !empty($relations[$userId][$groupId]) && $relations[$userId][$groupId]['role'] != 8) {
				$follow[$groupId] = 1;	
			}
			else {
				$follow[$groupId] = 0;	
			}
		}
		return $follow;
	}

	private function _getUserGroupRelationFromRedis($groupId, $userId) {
		if (empty($groupId) || empty($userId)) {
			return FALSE;
		}
		
		if (RedisUserGroupFollower::exists($userId) == FALSE) {
			return FALSE;
		}

		if ($userId == 219) {
			return FALSE;
		}
		//$groupId = 10557;$userId = 1698845;
        $insert = RedisUserGroupFollower::lInsert($userId, Redis::BEFORE, $groupId, $groupId); 
        if ($insert != -1 && !empty($insert)) {
            RedisUserGroupFollower::lRemove($userId, $groupId, 1);
            return TRUE;
        }
		return FALSE;
	}

	public function isGroupMember($groupId, $userId) {
		if (in_array($userId, array(879481,14041750,14089838,15032784))) {
			return TRUE;
		}
		$result = $this->getGroupRelation($groupId, $userId);
		if (empty($result)) {
			return FALSE;
		}	
		if ($result[$userId][0]['role'] == 1 || $result[$userId][0]['role'] == 0) {
			return TRUE;
		}
		return FALSE;
	}
	
	public function getGroupUserInfo($groupId, $role = 1, $fields = array('nickname', 'avatar_c'), $start = 0, $limit = 20) {
		if (empty($groupId)) {
			return FALSE;
		}
		if ($role == array(0,1) || $role == 1) {
			$groupCacheHelper = new GroupCache();
			$adminId = $groupCacheHelper->getGroupAdminCache($groupId);
		}
		if (!empty($adminId)) {
			$userIds[$groupId][$adminId['user_id']] = $adminId;
		}
		else {
			$userIds = $this->getGroupUsersByRole(array($groupId), array('user_id', 'group_id', 'role'), $role, $start, $limit);
		}
		if (empty($userIds[$groupId]) || !is_array($userIds[$groupId])) {
			return array();
		}
		$ids = array();
		foreach ($userIds[$groupId] AS $key => $value) {
			$ids[] = $userIds[$groupId][$key]['user_id'];
		}
		$userHelper = new User();
		$userInfos = $userHelper->getUserInfos($ids);
		foreach ($userInfos AS $key => $value) {
			foreach ($fields AS $field) {
				$userIds[$groupId][$key][$field] = $userInfos[$key][$field];
			}
		}
		if (!empty($userIds[$groupId]) && is_array($userIds[$groupId])) {
			foreach ($userIds[$groupId] AS $key => $value) {
				$userInfo[] = $userIds[$groupId][$key];
			}
		}
		return $userInfo;
	}


	public function getUserRole($groupId, $userId, $gAdmins) {
		$userInfo = $this->getGroupRelation($groupId, $userId);

		if (empty($userInfo)) {
			$userInfo = $this->isGroupFollower($groupId, $userId);
		}
		if ($userInfo == FALSE && !empty($userId)) {
			foreach($gAdmins as $key => $value) {
				//关注人主编并且未在杂志社的UnFollow名单(补救措施)
				if (RedisUserFollow::isFollowed($userId, $gAdmins[$key]['user_id']) && !RedisUserGroupUnFollower::sContains($groupId, $userId)) {
					RedisUserGroupFollower::lPush($userId, $groupId); 
					$userInfo = TRUE;
				}    
			}    
		}

		if (isset($userInfo[$userId][0]['role'])) {
			$role = $userInfo[$userId][0]['role'];
		}
		else if ($userInfo == TRUE) {
			$role = 5;
		}
		if (in_array($userId, $this->superUsers)) {
			$role = 1;
		}
		return $role;

	}

	public function getUserGroupNumber($userId, $role = array(0, 1), $fields = array('count(*) AS num')) {
		if (empty($userId)) {
			return FALSE;
		}
		if ($role == array(0, 1)) {
			$cacheKey = "UserEditGroupNum:" . $userId;
			$mem = Memcache::instance();
			$num = $mem->get($cacheKey);
			if (!empty($num)) {
				return $num;
			}
		}

		$paramsWhere = array(
			array(
				'key' => 'user_id',
				'value' => $userId,
				'operation' => 'eq'
			),
			array(
				'key' => 'role',
				'value' => $role,
				'operation' => 'in'
			)
		);
		$parameters = array(
			'where' => $paramsWhere,
			'fields' => $fields
		);
		$result = OperationDB::selectDataBase($parameters, 'GroupUser', '');
		$num = $result[0]['num'];
		if ($role == array(0, 1)) {
			$mem->set($cacheKey, $num, 600); 
		}
		return $num;
		
	}

	public function getGroupUserNumber($groupId, $role = array(0, 1), $fields = array('count(*) AS num')) {
		if (empty($groupId)) {
			return FALSE;
		}
		$paramsWhere = array(
			array(
				'key' => 'group_id',
				'value' => $groupId,
				'operation' => 'eq'
			),
			array(
				'key' => 'role',
				'value' => $role,
				'operation' => 'in'
			)
		);
		$parameters = array(
			'where' => $paramsWhere,
			'fields' => $fields
		);
		$result = OperationDB::selectDataBase($parameters, 'GroupUser', '');
		$num = $result[0]['num'];
		return $num;
		
	}
	/**
	 *
	 * @param $userId 用户编号
	 * @param $groupIds array 杂志社编号id数组
	 * @param $role array 权限数组
	 */
	public function delUserGroupsByRole($userId, $groupIds, $role = array(4, 5)) {
		if (empty($userId) || empty($groupIds) || empty($role) || !is_array($groupIds) || !is_array($role)) {
			return FALSE;
		}
		$role = implode(',', $role);
		$groupIds = implode(',', $groupIds);
		//$sqlComm = "DELETE FROM {$this->table} WHERE group_id IN (:_group_id) AND user_id=:_user_id AND role IN (:_role)";
		$sqlComm = "DELETE FROM {$this->table} WHERE group_id IN ($groupIds) AND user_id=$userId AND role IN ($role)";
		$sqlData = array();
		/*
		$sqlData = array(
			'_group_id' => $groupIds,
			'_user_id' => $userId,
			'_role' => $role,
		);*/	
		$affectedRows = DBGroupHelper::getConn()->write($sqlComm, $sqlData);
		return $affectedRows;
	}

	public function insertGroupUser($userId, $groupId, $role) {
		$result = $this->getGroupRelation($groupId, $userId, array('role'));
		$user = array();
		if (!isset($result[0]['role'])) {
			$fields = array(
				'user_id' => $userId,
				'group_id' => $groupId,
				'role' => $role
			);
			$user['fields'] = $fields;		
			$user['insert'] = 1;
			$result = OperationDB::operationDataBase($user, 'GroupUser', 'insert');
		}
		else {
			$user['fields']['role'] = $role;
			$conditions = array(
				'user_id' => $userId,
				'group_id' => $groupId
			);
			$user['condition'] = $conditions;
			$result = OperationDB::operationDataBase($user, 'GroupUser', 'update');
		}
		$groupCacheHelper = new GroupCache();
		if ($user['fields']['role'] == 0 || $user['fields']['role'] == 1) {
			$params = array($user['fields']);
			$groupCacheHelper->setGroupUserCache($params, $userId);
		}
		if ($role == 1) {
			$this->updateUserGroupFollower($groupId, $userId);
		}
		return TRUE;
	}

	public function insertGroupUserMultiple($userInfos) {
		$user = array();
		$user['fields']['m_insert'] = 1;
		$user['insert'] = 1;
		$row = array_keys($userInfos[0]);
		$row = implode(',', $row);
		$user['fields']['keys'] = $row;
		$values = array();
		$groupIds = array();
		foreach ($userInfos AS $key => $value) {
			$values[] = $userInfos[$key]['user_id'] . ',' . $userInfos[$key]['group_id'] . ',' . $userInfos[$key]['role'];
			$groupIds[] = $userInfos[$key]['group_id'];
		}
		$user['fields']['values'] = $values;
		$result = OperationDB::operationDataBase($user, 'GroupUser', 'insert');
		$fields['count_member'] = 'count_member+1';
		$fields['add_himself'] = 1;
		$groupHelper = new Groups();
		$groupHelper->updateGroupInfo($groupIds, $fields);
		return $result;
	}

	public function updateUserGroupFollower($groupId, $userId) {
        if (empty($userId) || empty($groupId)) {
            return;
        }    
		$followerUids = RedisUserFans::getFans($userId);
		//把粉丝中的不活跃用户移出
		foreach($followerUids as $key => $uid) {
			if (!RedisUserHomePosterTimeline::exists($uid)) {
				unset($followerUids[$key]);
			}    
		}    
		//第三个参数为去0,一段时间可去掉
		RedisUserGroupFollower::pushMultiUserGroupFollower($followerUids, $groupId);
	}

	private function _hackOtherGroups($groupId) {
		$role = 5; 
		$response = array(
			'type_now' => '已关注',
			'type_hover' => '取消关注',
		);
		if (in_array($groupId, $this->otherGroups)) {
			$response = array(
				'type_now' => '等待审核',
				'type_hover' => '取消申请',
			);
			$role = 4;
		}
		if (in_array($groupId, $this->enterGroups)) {
			$response = array(
				'type_now' => '已加入',
				'type_hover' => '退出杂志',
			);   
			$role = 0;
		}
		
		$result = array('response' => $response, 'role' => $role);
		return $result;
	}

	private function _hackOtherGroupsQuit($groupId) {
		$role = 5; 
		$response = array(
			'type_now' => '加关注',
			'type_hover' => '',
		);
		if (in_array($groupId, $this->otherGroups)) {
			$response = array(
				'type_now' => '加入我们',
				'type_hover' => '',
			);
			$role = 4;
		}
		else if (in_array($groupId, $this->enterGroups)) {
			$role = 0;
		}
		
		$result = array('response' => $response, 'role' => $role);
		return $result;
	}

	public function operationFollowGroups($userId, $groupIds) {
		if (empty($userId)|| empty($groupIds)) {
			return FALSE;
		}
		if ($this->isGroupFollower($groupIds, $userId)) {
			return TRUE;
		}
		foreach ($groupIds AS $groupId) {
			$this->operationFollowGroup($userId, $groupId);
		}
		return TRUE;
	}

	public function operationFollowGroup($userId, $groupId, $twitterNumber = 10) {
		if (empty($userId)|| empty($groupId)) {
			return FALSE;
		}
		if ($this->isGroupFollower($groupId, $userId)) {
			return TRUE;
		}
		$result = $this->_hackOtherGroups($groupId);
		$role = $result['role'];
		$response = $result['response'];
		$groupHelper = new Groups();
		$msgHelper = new UpdateUserNotice();
		$timelineHelper = new Timeline();
		$shareHelper = new ShareOb();
		$this->insertGroupUser($userId, $groupId, $role);
		RedisUserGroupFollower::lPush($userId, $groupId);
		$fields = array(
			'count_member' => 'count_member+1',
			'add_himself' => 1
		);
		$condition = array('group_id'=> $groupId);
		$groupHelper->updateGroupInfo(array($groupId), $fields, $condition, $userId);
		$msgHelper->addFollowGroupNotice($userId, $groupId);
		$timelineHelper->newGroupFollowing($userId, $groupId, 1209600, $twitterNumber);
		$settings = RedisUserConnectHelper::getUserSetting('qplus', $userId);
		$result = json_decode($settings, TRUE);
		if ( $result['sync_answer'] == 1 ) { 
			$flag = 'follow';
			$shareHelper->_qplusSync('qplus', $groupId, $userId);
		}
		return $response;
	}
	/**
	 *	删除该用户和杂志社关系的函数
	 *	@author : huazhulin@meilishuo.com
	 *	@param $userId 用户编号
	 *	@param $groupIds array 杂志社编号id数组
	 *	@param $role array 权限数组
	 */
	public function operationQuit($userId, $groupId) {
		if (!empty($userId)) {
			$params['user_id'] = $userId;
		}
		if (!empty($groupId)) {
			$params['group_id'] = $groupId;
		}
		$userInfo = $this->getGroupRelation(array($groupId), $userId);	
		if ($userInfo[$userId][0]['role'] == 1) {
			return FALSE;
		}
		$group = array(
			'condition' => $params
		);
		$groupHelper = new Groups();
		$fields = array(
			'count_member' => 'count_member-1',
			'add_himself' => 1
		);
		$condition = array('group_id'=> $groupId);
		$result = OperationDB::operationDataBase($group, 'GroupUser', 'delete');
		$result = $groupHelper->updateGroupInfo(array($groupId), $fields, $condition, $userId);
		if (!empty($userId)) {
			$this->operationQuitRedis($userId, $groupId);
			//timeline
			$groupTwitterHelper = new GroupTwitters();
			$groupTwitterHelper->operationDeleteTimelineTwitter($userId, $groupId);
		}
		$result = $this->_hackOtherGroupsQuit($groupId);
		$response = $result['response'];
		return $response;
	}
	
	public function operationQuitDB($group) {

		$domainObject = new TopicGroupUserObject($group);
		$domainObjectAssembler = new DomainObjectAssembler(GroupUserPersistenceFactory::getFactory('\Snake\Package\Group\GroupUserPersistenceFactory'));
		$groupCollection = $domainObjectAssembler->delete($domainObject);		
		return $groupCollection;
	}

	public function operationQuitRedis($userId, $groupId) {
		$result = $this->getGroupUsersByRole(array($groupId), array('user_id', 'group_id', 'role', 'created'));
		if (is_array($result[$groupId])) {
			foreach ($result[$groupId] AS $key => $value) {
				if(RedisUserFollow::isFollowed($userId, $key) == TRUE) {
					$adminFollow = TRUE;
				}
			}
		}
		if ($adminFollow == TRUE) {
			RedisUserGroupUnFollower::sAdd($groupId, $userId);
		}
		RedisUserGroupFollower::lRemove($userId, $groupId);
	}

	/**
	 *  关注杂志社函数	
	 *	@author : huazhulin@meilishuo.com
	 *	@param $userId 用户编号
	 *	@param $groupId  杂志社编号id
	 *	@param $role  权限
	 */
	public function operationFollow($userId, $groupId, $role) {
		$role = 5;
		$operationUserId = 0;	
		$relationHelper = new UserRelationGroup($userId, array($groupId));
		$changeHelper = new ChangeGroupRelation($userId, $operationUserId, $groupId, $role);
		$changeHelper->addObserver($relationHelper);
		$changeHelper->addObserver(new ClearGroupCache());
		$changeHelper->addObserver(new UserFollowGroupRedis());
		$changeHelper->addObserver(new UpdateUserNotice());
		$changeHelper->addObserver(new Timeline());
		$settings = RedisUserConnectHelper::getUserSetting('qplus', $userId);
		$result = json_decode($settings, TRUE);
		if ( $result['sync_answer'] == 1 ) { 
			$flag = 'follow';
			$changeHelper->addObserver(new ShareOb());
		//  $this->_qplusSync($flag, $this->group['group_id']);
		}   
		$changeHelper->runObserver();	
	}

	/**
	 * 取杂志社关注者的接口
	 * author : huazhulin
	 * pamras : $groupId(int)
	 * return : array
	 */
	public function getGroupFollower($groupId) {
		if(empty($groupId)) {
			return FALSE;
		}
		$admins = $this->getGroupUsersByRole(array($groupId), array('user_id', 'group_id', 'role', 'created'), array(4, 5, 8), 0, 30000);
		$adminIds = array();
		foreach ($admins[$groupId] AS $key => $value) {
			if ($admins[$groupId][$key]['role'] == 1) {
				$adminIds[] = $admins[$groupId][$key]['user_id'];
			}
			else if ($admins[$groupId][$key]['role'] == 8) {
				$blockIds[] = $admins[$groupId][$key]['user_id'];
			}
			else {
				$followIds[] = $admins[$groupId][$key]['user_id'];
			}
		}
		foreach ($adminIds AS $adminId) {
			if (!empty($followIds)) {
				$followIds = array_merge($followIds, RedisUserFans::getFans($adminId));
			}
			else {
				$followIds = RedisUserFans::getFans($adminId);
			}
		}
		$followIds = array_unique($followIds);
		$userGroupUnfollow = RedisUserGroupUnFollower::sMembers($groupId);
		if (!empty($blockIds)) {
			$followIds = array_diff($followIds, $blockIds);
		}
		if (!empty($blockIds)) {
			$followIds = array_diff($followIds, $userGroupUnfollow);
		}
		return $followIds;
	}

	public function sendInvitation($editorId, $nickname, $inviteIds, $groupId) {
		if(empty($editorId) || empty($inviteIds) || empty($groupId)) {
			return FALSE;
		}
		$result = $this->getGroupRelation($groupId, $editorId);
		if ($result[$editorId][0]['role'] != 1) {
			return FALSE;
		}
		//TODO send for msg
		$msgHelper = new Msg();
		$userHelper = new User();
		$groupHelper = new Groups();
		$inviteIds = explode(',', $inviteIds);
		$userInfos = $userHelper->getUserInfos($inviteIds);
		$groupInfo = $groupHelper->getGroupInfo(array($groupId));
		$groupInfo = $groupInfo[$groupId];

		foreach ($inviteIds AS $inviteId) {
			$code = $this->_formInvitationCode($editorId, $inviteId, $groupId);
			$this->_sendMsg($nickname, $inviteId, $userInfos[$inviteId]['nickname'], $groupId, $groupInfo['name'], $msgHelper, $code);
		}
		return TRUE;
	}

	public function checkInvitation($inviteId, $groupId) {
		$result = $this->_checkInvitationCode($groupId, $inviteId);
		if ($result == TRUE) {
			$role = 0;
			$this->insertGroupUser($inviteId, $groupId, $role);
		}
		return $result;
	}

	private function _sendMsg($nickname, $inviteId, $inviteNickname, $groupId, $groupName, $msgHelper, $code) {
		$url = MEILISHUO_URL . "/group/" . $groupId . "?icode=" . $code;
		$content = "你的好友@{$nickname} 邀请你加入杂志#{$groupName}#的编辑团队，点击以下链接接受邀请>>>" . $url ;
		$msgHelper->sendSysMsg($inviteId, $content);
		return TRUE;
	}

	private function _formInvitationCode($editorId, $inviteId, $groupId) {
		$result = RedisGroupInvitation::getInvitationId($groupId);
		if (count($result) < 50) {
			RedisGroupInvitation::insertInvitationId($groupId, $inviteId);
			$invitationCode = md5("invitation:" . $userId . ":" . $inviteId);
			return $invitationCode;
		}
	}

	private function _checkInvitationCode($groupId, $userId, $code = 1) {
		$result = RedisGroupInvitation::isInvited($groupId, $userId);
		if ($result == TRUE) {
			RedisGroupInvitation::removeInvitation($groupId, $userId);
			RedisGroupInvitation::insertInvitationId($groupId, 1);
		}
		return $result;
	}

	public function sortGroupsByGroupRank($groupIds, $fields = array("group_id, last_twitter_number"),$orderBy = "last_twitter_number DESC", $hashKey = "") {
		$Params = array();
		$paramsWhere = array(
			array(
				'operation' => 'in',
				'key' => 'group_id',
				'value' => $groupIds
				)
			);
		$paramsEx = array(
			'orderby' => $orderBy
			);
		$parameters = array(
			'where' => $paramsWhere,
			'ext_where' => $extCondition,
			'ext_condition' => $paramsEx,
			'fields' => $fields
		);
		if (!in_array($hashKey, $fields)) {
			$hashKey = "";
		}
		$result = OperationDB::selectDataBase($parameters, 'GroupRank', $hashKey);
		return $result;
	}

	public function getUserList($groupId, $userId, $role = 0, $offset = 0, $limit = 20) {
		if (empty($groupId) || empty($userId)) {
			return array();
		}
		$fields = array('user_id', 'group_id', 'role', 'created');
		$gUserIds = $this->getGroupUsersByRole(array($groupId), $fieds, $role, $offset, $limit);
		$gUserIds = $gUserIds[$groupId];
		$result = $this->getGroupRelation($groupId, $userId);
		if ($result[$userId][0]['role'] == 1) {
			$isMaster = 1;
		}
		$uIds = \Snake\Libs\Base\Utilities::DataToArray($gUserIds, 'user_id');
		$userHelper = new User();
		$fields = array('user_id', 'nickname', 'avatar_c');
		$userInfos = $userHelper->getUserInfos($uIds, $fields);
		foreach ($userInfos AS $key => $value) {
			$userId = $userInfos[$key]['user_id'];
			$role = $gUserIds[$userId]['role'];
			$userInfos[$key]['up'] = 0;
			$userInfos[$key]['down'] = 0;
			$userInfos[$key]['block'] = 0;
			$userInfos[$key]['kick'] = 0;
			if ($role == 0 && $isMaster == 1) {
				$userInfos[$key]['down'] = 1;
				$userInfos[$key]['block'] = 1;
				$userInfos[$key]['kick'] = 1;
			}
			else if ($role == 1) {
				$userInfos[$key]['down'] = 1;
				$userInfos[$key]['block'] = 1;
				$userInfos[$key]['kick'] = 1;
			}
		}
	}
	
	public function getUserGroups($userId) {
		if (empty($userId)) {
			return array();
		}
		$role = array(0,1);
		$fields = array('user_id', 'group_id', 'role', 'created');
		$orderBy = "role DESC, group_id DESC";
		$groupIds = $this->getUserGroupsByRole(array($userId), $role, $fields, $orderBy);
		$gIds = \Snake\Libs\Base\Utilities::DataToArray($groupIds[$userId], 'group_id');
		$groupHelper = new Groups();
		$groupInfos = $groupHelper->getGroupInfo($gIds);
		$gInfo = array();
		if (!empty($groupIds[$userId])) {
			foreach ($groupIds[$userId] AS $key => $value) {
				$groupId = $groupIds[$userId][$key]['group_id'];
				$gInfo[$key]['group_id'] = $groupId;
				$gInfo[$key]['name'] = str_replace('"', '', $groupInfos[$groupId]['name']);
				//$gInfo[$key]['name'] = htmlspecialchars($gInfo[$key]['name']);
				$gInfo[$key]['role'] = $groupIds[$userId][$key]['role'];
			}
		}
		$groupName = \Snake\Libs\Base\Utilities::DataToArray($gInfo, 'name');
		$userHelper = new User();
		$userInfo = $userHelper->getUserInfos(array($userId));
		$defaultName = $userInfo[$userId]['nickname'] . '喜欢的宝贝';
		if (!in_array($defaultName, $groupName)) {
			$addInfo['group_id'] = 0;
			$addInfo['name'] = $defaultName;
			$addInfo['role'] = 1;
			$gInfo[] = $addInfo;
		}
		return $gInfo;
	}
}
