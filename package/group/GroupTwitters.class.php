<?php
/**
 *  取杂志社中推的package
 *	@author huazhulin@meilishuo.com
 *	@since 2012-5
 *	@version 1.0
 */
namespace Snake\Package\Group;
Use \Snake\Libs\PlatformService\MlsStorage;
Use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Twitter\Helper\DBTwitterHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;
Use \Snake\Package\User\User;
Use \Snake\Package\Group\Groups;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Group\GroupTwitterPersistenceFactory;
Use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline;


class GroupTwitters {

	public function __construct(){

	}

	/**
	 *	取推所在的杂志社，推的作者昵称，如果存在原推的话，再取原推杂志社和作者昵称
	 *	@params : twitterId, twitterType, twitterSourceId
	 *	@return : array()
	 */
	public function getTwitterToAndFrom($twitterId, $twitterType, $twitterSourceId = 0) {
        $groupInfo = $this->getGroupTwitter(array($twitterId, $twitterSourceId));
        $aUserId = $groupInfo[$twitterId]['user_id'];
        $sUserId = $groupInfo[$twitterSourceId]['user_id'];
        $userHelper = new User();
        $authorInfo = $userHelper->getUserInfo($aUserId);
        $sourceAuthorInfo = $userHelper->getUserInfo($sUserId);
        $groupTo = $groupInfo[$twitterId]['group_id'];
        $groupFrom = isset($groupInfo[$twitterSourceId]['group_id']) ? $groupInfo[$twitterSourceId]['group_id'] : 0;
		$groupHelper = new Groups();
		$groupName = $groupHelper->getGroupInfo(array($groupTo, $groupFrom));
		$params = array(
			'aUserId' => $aUserId,
			'sUserId' => $sUserId,
			'aNickname' => $authorInfo['nickname'],
			'sNickname' => $sourceAuthorInfo['nickname'],
			'fGroupName' => $groupName[$groupFrom]['name'],
			'tGroupName' => $groupName[$groupTo]['name']
			);
		return $params;

	}


	/**
	 *	取推所在的杂志社id
	 *	@params: twitterIds, fields
	 *	@return: array()
	 */
	public function getGroupTwitter($twitterIds = array(), $fields = array('twitter_id', 'group_id', 'user_id', 'show_type', 'twitter_show_type'), $hashKey = 'twitter_id') {
		if (empty($twitterIds)) {
			return FALSE;
		}
		$groupCacheHelper = new GroupCache();
		$cacheTwtters = $groupCacheHelper->getGroupTwitterRelationCache($twitterIds, $hashKey);
		if (count($cacheTwtters) >= count($twitterIds)) {
			return $cacheTwtters;
		}
		$identityObject = new IdentityObject();
		$identityObject->field('twitter_id')->in($twitterIds)->field('show_type')->eq(0);
		$identityObject->col($fields);
		$domainObjectAssembler = new DomainObjectAssembler(GroupTwitterPersistenceFactory::getFactory('\Snake\Package\Group\GroupTwitterPersistenceFactory'));
		$groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
        $twitters = array();
        while ($groupCollection->valid()) {
            $groupObj = $groupCollection->next();   
			if (empty($hashKey)) {
				$twitters[] = $groupObj->getGroup();
			}
			else {
				$twitters[$groupObj->getTwitterId()] = $groupObj->getGroup();
			}
        }
		if (!empty($cacheTwtters)) {
			$twitters = $cacheTwtters + $twitters;
		}
		return $twitters;
	}

	/**
	 *	取杂志社推的方法
	 *  @params : groupIds, fields
	 *	@return : array()
	 */
	public function getGroupTwitters($groupIds = array(), $fields = array('twitter_id', 'group_id', 'elite'), $start = 0, $limit = 20, $orderBy = 'twitter_id desc', $hashKey = 'group_id', $showType = 0, $havePicture = 1) {
		if (empty($groupIds)) {
			return FALSE;
		}
		$groupInfo = array();
		$startO = $start;
		$limitO = $limit;
		foreach ($groupIds AS $groupId) {
			$start = $startO;
			$limit = $limitO;
			if ($fields != array('/*GroupTwitters-lhz*/count(*) AS num')) {
				if ($start < 120) {
					$groupCacheHelper = new GroupCache();
					$twitters = $groupCacheHelper->getGroupFistPageTwitterCache($groupId, $start, $limit);
					if (!empty($twitters) && count($twitters) >= $limit) {
						$groupInfo[$groupId] = $twitters;
						continue;
					}
					/*if (!empty($twitters)) {
						$start = $start + count($twitters);
						$limit = $limit - count($twitters);
					}*/
				}
				if ($start == 0) {
					$cacheTwitters = $groupCacheHelper->getGroupTwitterCache($groupId);
					if (count($cacheTwitters) >= $limit) {
						$cacheTwitters = array_slice($cacheTwitters, 0 , $limit);
					}
					if (!empty($cacheTwitters)) {
						$limit2 = $limit;
					}
				}
			}
            if ($start == 0) {
			    $limit1 = $limit + 100;
            }
            else {
                $limit1 = $limit;
            }
			$groups = array();
			$groups = $this->getGroupTwittersByGroupIdsNoCache(array($groupId), array('twitter_id', 'group_id', 'elite', 'created'), $start, $limit1, $orderBy, '', $showType, $havePicture);
			/*$identityObject = new IdentityObject();
			$identityObject->field('group_id')->eq($groupId)->field('show_type')->eq($showType)->field('have_picture')->eq($havePicture)->orderby($orderBy)->limit($start . ', ' . $limit1);
			$identityObject->col($fields);

			$domainObjectAssembler = new DomainObjectAssembler(GroupTwitterPersistenceFactory::getFactory('\Snake\Package\Group\GroupTwitterPersistenceFactory'));
			$groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
			$groups = array();
			while ($groupCollection->valid()) {
				$groupObj = $groupCollection->next();   
				$groups[] = $groupObj->getGroup();
			}*/
			if ($fields == array('/*GroupTwitters-lhz*/count(*) AS num')) {
				$groupInfo[$groupId] = $groups;
				break;
			}
			if (!empty($cacheTwitters)) {
				$limit = $limit2;
				$groups = array_merge($cacheTwitters, $groups);
			}
			if (empty($groups)) {
				$groupInfo[$groupId] = array();
				continue;
			}
			$tIds = array();	
			$groups = $this->_arraySort($groups, 'created', 'DESC');
			foreach ($groups AS $k => $v) {
				$tIds[] = $groups[$k]['twitter_id'];
			}

			$tIds = array_unique($tIds);
			//arsort($tIds);
			$tIds = array_values($tIds);
			if ($start == 0 && $fields != array('/*GroupTwitters-lhz*/count(*) AS num') && !empty($tIds)) {
				$cacheKey = "GroupTwitters:FirstPage:" . $groupId; 
				$cache = Memcache::instance();
				$cache->set($cacheKey, $tIds, 60*5);
			}
			$tIds = array_slice($tIds, 0, $limit);
			//if (!empty($groups) && $fields != array('/*GroupTwitters-lhz*/count(*) AS num')) {
			/*	$groupInfo[$groupId] = array_slice($groups, 0, $limit);
			}
			else {*/
			$groupInfo[$groupId] = $tIds;
			//}


		}
		return $groupInfo;
	}

	private function _arraySort($arr, $keys, $type='asc'){
		$keysvalue = $newArray = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if ($type == 'asc') {
			asort($keysvalue);
		}
		else {
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k => $v){
			$newArray[$k] = $arr[$k];
		}
		return $newArray;
	}

	/**
	* 不需要从cache里取杂志社的推
	* @param $groupIds $fields
	* @return array()
	**/
	public function getGroupTwittersByGroupIdsNoCache($groupIds = array(), $fields = array('twitter_id', 'group_id', 'elite'), $start = 0, $limit = 20, $orderBy = 'twitter_id desc', $hashKey = '', $showType = 0, $havePicture = 1) {
		$paramsWhere = array(
			array(
				'operation' => 'in',
				'key' => 'group_id',
				'value' => $groupIds
			),
			array(
				'operation' => 'eq',
				'key' => 'show_type',
				'value' => $showType 
			),
			array(
				'operation' => 'eq',
				'key' => 'have_picture',
				'value' => $havePicture
			)
		);
		$ex['orderby'] = $orderBy;
		$ex['limit'] = "$start , $limit";
		$parameters = array(
			'where' => $paramsWhere,
			'ext_condition' => $ex,
			'fields' => $fields
		);
		if (!in_array($hashKey, $fields)) {
			$hashKey = "";
		}
		$groups = OperationDB::selectDataBaseHV($parameters, 'GroupTwitter', $hashKey);
		return $groups;
	}

	public function getGroupsTwitterNumbers($groupIds) {
		if (empty($groupIds)) {
			return FALSE;
		}		
		$cacheKey = "GroupTwitters:Number" . md5(implode(',', $groupIds));
		$cacheHelper = Memcache::instance();
		$twitterNumber = $cacheHelper->get($cacheKey);
		$twitterNumber = array();
		if (!empty($twitterNumber[$groupId])) {
			return $twitterNumber;
		}
		$fields = array('/*GroupTwitters-lhz*/count(*) AS num', 'group_id');
        $paramsWhere = array(
            array(
                'operation' => 'in',
                'key' => 'group_id',
                'value' => $groupIds
                ),
			array(
				'operation' => 'eq',
				'key' => 'show_type',
				'value' => '0'
			),
			array(
				'operation' => 'eq',
				'key' => 'have_picture',
				'value' => '1'
			)
            );
		$paramsEx = array(
			'groupby' => 'group_id'	
		);
        $parameters = array(
            'where' => $paramsWhere,
            'ext_where' => $extCondition,
			'ext_condition' => $paramsEx,
            'fields' => $fields
        );
        $hashKey = "group_id"; 
        $result = OperationDB::selectDataBaseHV($parameters, 'GroupTwitter', $hashKey);
		if (!empty($result)) {
			$cacheHelper->set($cacheKey, $result, 600);
		}
		return $result;
	}

	public function getGroupTwitterPic($groupIds) {
		$groupHelper = new Groups();
		foreach ($groupIds AS $groupId) {
			$groupBaseInfo = $groupHelper->getGroupInfo(array($groupId), array('group_id', 'name'));
			if (empty($groupBaseInfo)) {
				continue;
			}
			$groupInfos[$groupId] = $groupBaseInfo[$groupId];
			$twitters = $this->getGroupTwitters(array($groupId), array('twitter_id', 'group_id', 'elite'), 0, 12);
			$twitters = $twitters[$groupId];
			$tIds = array();
			/*foreach ($twitters[$groupId] AS $k => $v) {
				$twitterIds[] = $twitters[$groupId][$k]['twitter_id'];
				$tIds[] = $twitters[$groupId][$k]['twitter_id'];
			}*/
			foreach ($twitters AS $twitter) {
				$twitterIds[] = $twitter;
			}
			$tIds = $twitters;
			$groupTwitters[$groupId] = $tIds;
		}
		if (empty($groupInfos)) {
			return FALSE;
		}

		$twitterHelper = new Twitter();
		$twitterInfo = $twitterHelper->getPicturesByTids($twitterIds, "c");
		foreach ($groupInfos AS $key => $value) {
			$picUrl = array();
			foreach ($groupTwitters[$key] AS $k => $v) {
				$twitterId = $groupTwitters[$key][$k];
				if (!empty($twitterInfo[$twitterId]['n_pic_file'])) {
					$picUrl[] = $twitterInfo[$twitterId]['n_pic_file'];
				}
			}
			if (!empty($picUrl)) {
				$picUrl = array_slice($picUrl, 0, 7);
				$groupInfos[$key]['huge_picture_url'] = $picUrl[0];
				array_splice($picUrl, 0, 1);
				$groupInfos[$key]['little_picture_url'] = $picUrl;
			}
			else {
				unset($groupInfos[$key]);
			}
		}
		return $groupInfos;
	}

	public function updateTwitterHtmlContent($twitterInfo) {
		$groupTopicHelper = new GroupTopics();
		$twitterHtmlContent = $twitterInfo['twitter_htmlcontent'];
		$userId = $twitterInfo['twitter_author_uid'];
		$groupId = $twitterInfo['group_id'];
		$topicTitle = $this->checkIfHaveTopicsTag($twitterHtmlContent);
		$title = str_replace('#', '', $topicTitle[0]);
		if (empty($groupId) && empty($title)) {
			return $twitterInfo;
		}
		$topicId = $groupTopicHelper->hackGroupTopic($title, $userId, $groupId, $twitterId);
		if (empty($topicId)) {
			return $twitterInfo;
		}
		$linkStr = "<a href='/group/{$groupId}?topic_id={$topicId}' target='_blank'>{$topicTitle[0]}</a>";
		$twitterHtmlContent = str_replace($topicTitle[0], $linkStr, $twitterHtmlContent);
		$twitterInfo['twitter_htmlcontent'] = $twitterHtmlContent;
		$twitter['twitter_info'] = $twitterInfo;
		$twitter['group']['topic_id'] = $topicId;
		return $twitter;
	}

    protected function checkIfHaveTopicsTag($content) {
		$partten = "/(\#.*\#)/siU";
		$matchArr = array(); 
		preg_match_all($partten, $content, $matchArr);
		$tmpArray = array_unique ($matchArr[0]);
		return $tmpArray;
    } 
	/**
	* 向杂志社插入一条推 
	* @author : huazhulin
	* @param : $groupId(int)
	* @param : $twitterId(int)
	* @param : $userId(int)
	* @param : $havePicture(int)
	* @param : $twitterShowType(int)
	* @return array()
	**/
	public function hackTopicGroupTwitter($twitter) {
		$twitterInfo = $twitter['twitter_info'];
		$groupInfo = $twitter['group'];
		$groupId = $twitterInfo['group_id'];
		$twitterId = $twitterInfo['twitter_id'];
		$userId = $twitterInfo['twitter_author_uid'];
		$twitterShowType = $twitterInfo['twitter_show_type'];
		$havePicture = 0;
		if (!empty($twitterInfo['pid']) || !empty($twitterInfo['twitter_goods_id'])) {
			$havePicture = 1;
		}
		$topicId = isset($groupInfo['topic_id']) ? $groupInfo['topic_id'] : 0;
		if (empty($groupId) || empty($twitterId) || empty($userId)) {
			return FALSE;
		}
		$this->operationInsertTwitter($groupId, $twitterId, $userId, $havePicture, $twitterShowType, $topicId);
		return TRUE;
	}

	private function operationInsertTwitter($groupId, $twitterId, $userId, $havePicture, $twitterShowType = 2, $topicId = 0) {
		$params = array(
			'fields' => array(
				'group_id' => $groupId,
				'twitter_id' => $twitterId,
				'user_id' => $userId,
				'topic_id' => $topicId,
				'have_picture' => $havePicture,
				'twitter_show_type' => $twitterShowType,
				'created' => time()
			),
			'insert' => TRUE
		);
		$this->insertGroupTwitter($params);
		return TRUE;
	}

	private function insertGroupTwitter($params) {
		OperationDB::operationDataBase($params, 'GroupTwitter', 'insert');
		$groupCacheHelper = new GroupCache();
		$twitterInfo[0] = array(
			'group_id' => $params['fields']['group_id'],
			'twitter_id' => $params['fields']['twitter_id'],
			'elite' => 0
		);
		if (!empty($params['fields']['have_picture'])) {
			$groupCacheHelper->setGroupTwitterCache($twitterInfo);
			$groupCacheHelper->setGroupTwitterRelationCache($twitterInfo[0]);
			$groupCacheHelper->updateGroupCacheForTwitter($params['fields']['group_id']);
		}
		return TRUE;
	}

	public function deleteGroupTwitters($groupId = 0, $twitterId = 0) {
		$groupCacheHelper = new GroupCache();
		$fields = array(
			'show_type' => 1
		);
		if (!empty($groupId)) {
			$condition = array(
				'group_id' => $groupId
			);
		}
		if (!empty($twitterId)) {
			$condition['twitter_id'] = $twitterId;
		}
		if (empty($condition)) {
			return FALSE;
		}
		$params = array(
			'fields' => $fields,
			'condition' => $condition
		);
		OperationDB::operationDataBase($params, 'GroupTwitter', 'update');
		$groupCacheHelper->updateGroupCacheForTwitter($groupId);
		return TRUE;
	}

	public function operationDeleteTimelineTwitter($userId, $groupId) {
		$twitterIds = $this->getGroupTwittersByGroupIdsNoCache(array($groupId), array('twitter_id', 'group_id', 'elite'), 0, 3600);
		if (empty($twitterIds)) {
			return TRUE;
		}
		$tIds = array();
		foreach ($twitterIds AS $key => $value) {
			$tIds[] = $twitterIds[$key]['twitter_id'];
		}
		RedisUserHomePosterTimeline::removeTwitters($userId, $tIds);
		return TRUE;
	}
}

