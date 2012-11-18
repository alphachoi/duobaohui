<?php
namespace Snake\Package\Group;

Use \Snake\libs\Cache\Memcache;

class GroupCache {

	private $cache = NULL;
	
	public function __construct(){
		if (empty($this->cache)) {
			$this->cache = Memcache::instance();
		}
	}
	
	public function getGroupTwitterRelationCache($twitterIds = array(), $hashKey) {
		if (empty($twitterIds)) {
			return FALSE;
		}
		$twitters = array();
		foreach ($twitterIds AS $twitterId) {
			$cacheKey = "TOPIC_GROUP_TWITTER_RELATION_" . $twitterId;
			$cacheResult = $this->cache->get($cacheKey);
			if (!empty($cacheResult)) {
				if ($hashKey == 'twitter_id') {
					$twitters[$twitterId] = $cacheResult;
				}
				else {
					$twitters[] = $cacheResult;
				}
			}
		}
		return $twitters;

	}

	public function setGroupTwitterRelationCache($twitterInfo) {
		if (empty($twitterInfo)) {
			return FALSE;
		}
		$twitterId = $twitterInfo['twitter_id'];
		$cacheKey = "TOPIC_GROUP_TWITTER_RELATION_" . $twitterId;
		$this->cache->set($cacheKey, $twitterInfo, 7200);
		return;
	}

	public function getGroupInfoCache($groupIds, $hashKey) {
		$groups = array();
		foreach ($groupIds AS $groupId) {
			$cacheKey = "TOPIC_GROUP_" . $groupId;
			$cacheResult = $this->cache->get($cacheKey);
			if (!empty($cacheResult)) {
				if ($cacheResult['header_path'] != 'glogo/_o/77/62/5ca16a2ba61b5933a50dbbc9444a_942_248.jpg' && !empty($cacheResult['header_path'])) {                
					$cacheResult['header_path'] = \Snake\Libs\Base\Utilities::getPictureUrl($cacheResult['header_path'], '_o');
				}
				if($hashKey == 'group_id') {
					$groups[$groupId] = $cacheResult;
				}
				else {
					$groups[] = $cacheResult;
				}
			}
		}
		return $groups;
	}

	public function setGroupInfoCache($groupInfo) {
		$groupId = $groupInfo['group_id'];
		$cacheKey = "TOPIC_GROUP_" . $groupId;
		$this->cache->set($cacheKey, $groupInfo, 3600);
		return TRUE;
	}

	public function getGroupFistPageTwitterCache($groupId, $start = 0, $limit = 20) {
		$twitters = array();
		$twitter = array();
		$cacheKey = "GroupTwitters:FirstPage:" . $groupId;
		$twitters = $this->cache->get($cacheKey);
		if (!empty($twitters)) {
			$twitters = array_slice($twitters, $start, $limit);
		}
		return $twitters;
	}

	public function getGroupTwitterCache($groupId) {
		$cacheKey = "TOPIC_GROUP_TWITTER_" . $groupId;
		$cache = Memcache::instance();
		$twitters = $cache->get($cacheKey);
		if (!empty($twitters)) {
			$twitters = array_reverse($twitters);
		}
		return $twitters;
	}
	
	public function setGroupTwitterCache($twitterInfo) {
		if (empty($twitterInfo)) {
			return;
		}
		$groupId = $twitterInfo[0]['group_id'];
		$cacheKey = "TOPIC_GROUP_TWITTER_" . $groupId;
		$twitters = $this->cache->get($cacheKey);
		if (count($twitters) > 120) {
			array_shift($twitters);
		}
		if(!empty($twitters)) {
			$twitters = array_merge($twitterInfo, $twitters);
		}
		else {
			$twitters = $twitterInfo;
		}
		$this->cache->set($cacheKey, $twitters, 3600);
		return;
	}

	public function updateGroupCacheForTwitter($groupId) {
		if (empty($groupId)) {
			return;
		}
		$cacheKey = "GroupTwitters:FirstPage:" . $groupId;
		$this->cache->delete($cacheKey);
		return TRUE;	
	}

	public function updateGroupCacheFromGroup($groupId, $userId = 0) {
		if (empty($groupId)) {
			return;
		}
		$cacheKey = "TOPIC_GROUP_" . $groupId;
		$this->cache->delete($cacheKey);
		$cacheKey = "TOPIC_GROUP_USER_GROUP_" . $groupId;
		$this->cache->delete($cacheKey);
		$cacheKey = "TOPIC_GROUP_TWITTER_" . $groupId;
		$this->cache->delete($cacheKey);
		$cacheKey = "GROUP_HEADER_CACHE" . $groupId;
		$this->cache->delete($cacheKey);
		$cacheKey = "GROUP_CACHE_MAIN_PAGE_MIX" . $groupId;
		$this->cache->delete($cacheKey);
		if (!empty($userId)) {
			$cacheKey = "USER_GROUP_IDS:" . $userId . ":editor";
			$this->cache->delete($cacheKey);
			$cacheKey = "UserEditGroupNum:" . $userId;
			$this->cache->delete($cacheKey);
			$cacheKey = "USER_GROUP_IDS:" . $userId . ":follower";
			$this->cache->delete($cacheKey);
		}
		return TRUE;
	}

	public function setGroupUserCache($user, $userId) {
		$cacheKey = "UserEditGroupNum:" . $userId;
		$this->cache->delete($cacheKey);
		$cacheKey = "TOPIC_GROUP_USER_" . $userId;
		$result = $this->cache->get($cacheKey);
		if (!empty($result)) {
			$result = array_merge($result, $user);
		}    
		else {
			$result = $user;
		}    
		$this->cache->set($cacheKey, $result, 3600);	
	}
	
	public function getGroupUserCache($userId) {
		$cacheKey = "TOPIC_GROUP_USER_" . $userId;
		$result = $this->cache->get($cacheKey);
		return $result;
	}

	public function getGroupAdminCache($groupId) {
		$cacheKey = "TOPIC_GROUP_USER_GROUP_" . $groupId;
		$result = $this->cache->get($cacheKey);
		return $result[0];
	}
	
}

