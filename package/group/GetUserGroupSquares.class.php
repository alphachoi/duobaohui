<?php
namespace Snake\Package\Group;

Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;
Use \Snake\Package\Group\GroupTwitterPersistenceFactory;
Use \Snake\Package\Group\GroupFactory;
Use \Snake\Package\Group\GroupCache;
Use \Snake\Package\Group\Helper\RedisUserGroupFollower;
Use \Snake\Package\Group\Groups;
Use \Snake\Package\Group\GroupUser;


class GetUserGroupSquares {
	
	public function getUserGroupSquaresByRole($userId, $role = array(0, 1), $start = 0, $limit = 20, $vUserId = 0) {
		if (empty($userId) || empty($role)) {
			return FALSE;
		}
		$mem = Memcache::instance();
		$cacheKey = "USER_GROUP_IDS:" . $userId;
		if ($role == array(0, 1)) {
			$cacheKey .= ":editor";
		}
		else {
			$cacheKey .= ":follower";
		}
		$groupIds = $mem->get($cacheKey);
		//$groupIds = array();
		if (empty($groupIds)) {
			$userGroupHelper = new GroupUser();
			$gIds = $userGroupHelper->getUserGroupsByRole(array($userId), $role, array('user_id', 'group_id', 'role', 'created'), "role DESC");
			$gIds = $gIds[$userId];
			$groupIds = $this->sortGroups($gIds);
			$mem->set($cacheKey, $groupIds, 3600);
		}
		
		$groupInfo = array();
		if (!empty($groupIds)) {
			$groupIds = array_slice($groupIds, $start, $limit);
			$mGIds = implode(',', $groupIds);
			$mGIds = $mGIds . ":" . $userId;
			$cacheKey = "USER_GROUP_INFOS:" . md5($mGIds);
			$groupInfo = $mem->get($cacheKey);
			//$groupInfo = array();
			$groupHelper = new Groups();
			if (empty($groupInfo)) {
				if ($vUserId != $userId) {
					$groupInfo = $groupHelper->getGroupSquareInfo($groupIds);
					$mem->set($cacheKey, $groupInfo, 600);
				}
				else {
					$groupInfo = $groupHelper->getGroupSquareInfo($groupIds, $userId);
				}
			}
			if (!empty($vUserId)) {
				$groupInfo = $groupHelper->fillSquare($groupInfo, $vUserId);
			}
			$this->fixGroupNumber($groupIds, $groupInfo, $userId);		
		}
		return $groupInfo;
	}

	public function getUserFollowGroupSquares($userId, $start = 0, $limit = 20, $vUserId = 0) {
		if (empty($userId)) {
			return FALSE;
		}
		$mem = Memcache::instance();

		$gIds = RedisUserGroupFollower::getFollowGroups($userId, $start, $limit);
		$groupIds = array_filter($gIds);
		$groupIds = array_values($groupIds);

		if (count($gIds) != count($groupIds)) {
			RedisUserGroupFollower::removeMultiFollowGroups(array($userId), "");	
		}

		
		//$groupIds = $this->sortGroupsByGroupRank($groupIds);
		//$groupIds = array_slice($groupIds, $start, $limit);
		$mGIds = implode(',', $groupIds);
		$mGIds = $mGIds . ":" . $userId;
		$cacheKey = "USER_FOLLOW_GROUP_INFOS:" . md5($mGIds);
		$groupInfo = $mem->get($cacheKey);
		$groupHelper = new Groups();
		if (empty($groupInfo)) {
			$groupInfo = $groupHelper->getGroupSquareInfo($groupIds);
			$mem->set($cacheKey, $groupInfo, 600);
		}
		if (!empty($vUserId)) {
			if (!empty($groupInfo) && $vUserId == $userId) {
				foreach ($groupInfo as $key => $gInfo) {
					$groupInfo[$key]['is_follower'] = 1;
				}
			}
			else {
				$groupInfo = $groupHelper->fillSquare($groupInfo, $vUserId);
			}
		}
		$this->fixGroupNumber($groupIds, $groupInfo, $userId);
		return $groupInfo;
	}


	public function sortGroups($gIds) {
		if (empty($gIds)) {
			return FALSE;
		}
		$EditorInChief = array();
		$Editor = array();
		foreach($gIds AS $key => $value) {
			if ($gIds[$key]['role'] == 1) {
				$EditorInChief[] = $gIds[$key]['group_id'];
			}
			else {
				$Editor[] = $gIds[$key]['group_id'];
			}
		}
		$EditorInChief = $this->sortGroupsByGroupRank($EditorInChief);
		$Editor = $this->sortGroupsByGroupRank($Editor);
		if (!empty($EditorInChief) && !empty($Editor)) {
			$groupIds = array_merge($EditorInChief, $Editor);
		}
		else if (empty($Editor)) {
			$groupIds = $EditorInChief;
		}
		else {
			$groupIds = $Editor;
		}
		return $groupIds;

	}

	public function sortGroupsByGroupRank($groupIds, $fields = array('group_id'), $orderBy = "last_twitter_number DESC") {
		if (empty($groupIds)) {
			return FALSE;
		}
		$identityObject = new IdentityObject();	
		$identityObject->field('group_id')->in($groupIds)->orderby($orderBy);
		$identityObject->col($fields);
		$domainObjectAssembler = new DomainObjectAssembler(GroupRankPersistenceFactory::getFactory('\Snake\Package\Group\GroupRankPersistenceFactory'));
		$groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$groups = array();
		while ($groupCollection->valid()) {
			$groupObj = $groupCollection->next();
			$groups[] = $groupObj->getGroupId();
		}
		if (!empty($groups)) {
			$groups = array_unique(array_merge($groups, $groupIds));
			$gIds = array();
			$gIds = array_values($groups);
		}
		else {
			$gIds = array();
			$gIds = $groupIds;
		}
		return $gIds;

	}

	public function fixGroupNumber($groupIds, $groupInfo, $userId) {
		foreach ($groupIds AS $groupId) {
			if (empty($groupInfo[$groupId])) {
				RedisUserGroupFollower::lRemove($userId, $groupId);					
				RedisUserGroupFollower::lRemove($userId, "");
			}
		}
	}

}



