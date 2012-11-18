<?php
namespace Snake\Package\Group;


/**
 *	取杂志社相关信息的package
 *	@author : huazhulin
 *	@since : 2012-6-10
 *	@mail : huazhulin@meilishuo.com
 *	@version : 1.0
 */

//Use \Snake\Libs\PlatformService\MlsStorage;
//Use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Group\Helper\DBGroupHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;
Use \Snake\Package\Base\DomainObject;
Use \Snake\Package\Group\GroupTwitterPersistenceFactory;
Use \Snake\Package\Group\GroupFactory;
Use \Snake\Package\Group\GroupCache;
Use \Snake\Package\User\User;
Use \Snake\Package\User\Helper\RedisUserFans;
Use \Snake\Package\User\Helper\RedisUserFollow;
Use \Snake\Package\Group\Helper\RedisUserGroupUnFollower;
Use \Snake\Package\Group\Helper\RedisUserGroupFollower;
Use \Snake\Package\Group\Helper\RedisGroupInvitation;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Group\GroupTwitters;
Use \Snake\Package\Cms\CmsIndexWelcome;
Use \Snake\Package\Cms\CmsIndexType;
Use \Snake\Package\Timeline\Outbox\RedisGroupPosterOutbox;
Use \Snake\Package\Recommend\Recommend;
Use \Snake\Package\User\UserRelation;
use \Snake\Package\Group\GroupMainCatalog;
use \Snake\Package\Group\GroupCatalog;
use \Snake\Package\Group\GroupTopics;

/**
 *	取杂志社相关信息
 *	@author : huazhulin
 */
class Groups {

	private $groupIds = array();
	private $superUsers = array(219,1751,1431119,1765,1698845,1590448,1714106,3896618, 7579460,7222759, 6140112, 10918214);
	private $otherGroups = array(2,5,6,7,8,10,11,12,13,14,15,16,17,18, 13354871, 13232492, 15100011, 14898409, 15127179, 15422355, 13321802, 11212, 16274008, 16274019, 16274026, 16274047);
	private $insertGroups = array();
	private $showCollectionGroups = array(19108, 14067, 15671983, 15670190, 15820756, 15816321, 16102049, 16181353, 16114091, 16217111, 16284629, 16284104, 16277193, 16287770, 16294527, 16285488, 16277861);
	private $showHeaderCollectionGroups = array(15816321, 15671983);
	public function __construct(){

	}
	

	/** 
	 *	取杂志信息
	 *	@author : huazhulin
	 *	@params : $groupIds, $fields, $hashKey
	 *	@return : array()
	 */
	public function getGroupInfo($groupIds, $fields = array('group_id', 'name'), $hashKey = 'group_id') {
		if(empty($groupIds)) {
			return FALSE;
		}
		$groupCacheHelper = new GroupCache();
		$cacheGroups = $groupCacheHelper->getGroupInfoCache($groupIds, $hashKey);
		if (count($cacheGroups) >= count($groupIds)) {
			/*foreach ($cacheGroups AS $key => $value) {
				$cacheGroups[$key]['name'] = htmlspecialchars_decode($cacheGroups[$key]['name']);
				$cacheGroups[$key]['name'] = htmlspecialchars_decode($cacheGroups[$key]['name']);
			}*/
			return $cacheGroups;
		}
		foreach ($cacheGroups AS $key => $value) {
			foreach ($groupIds AS $k) {
				if ($cacheGroups[$key]['group_id'] == $groupIds[$k]) {
					unset($groupIds[$k]);
					break;
				}
			}
		}
		$paramsWhere = array(
			array(
				'operation' => 'in',
				'key' => 'group_id',
				'value' => $groupIds
			),
			array(
				'operation' => 'in',
				'key' => 'group_attr',
				'value' => array(1)
			)
		);
		$parameters = array(
			'where' => $paramsWhere,
			'ext_where' => $extCondition,
			'fields' => $fields
		);
		if (!in_array($hashKey, $fields)) {
			$hashKey = "";
		}

		$groups = OperationDB::selectDataBaseHV($parameters, 'Group', $hashKey);
		if (empty($groups) && empty($cacheGroups)) {
			return array();
		}
        if (!empty($cacheGroups) && is_array($cacheGroups) && is_array($groups)) {
            $groups = $cacheGroups + $groups;
        }   
        elseif (empty($groups) && !empty($cacheGroups) && is_array($cacheGroups)) {
            $groups = $cacheGroups;
        }
		if (empty($groups)) {
			return array();
		}
		/*foreach ($groups AS $key => $value) {
			$groups[$key]['name'] = htmlspecialchars_decode($groups[$key]['name']);
			$groups[$key]['name'] = htmlspecialchars_decode($groups[$key]['name']);
		}*/
		foreach ($groups AS $key => $value) {
			if ($groups[$key]['header_path'] != 'glogo/_o/77/62/5ca16a2ba61b5933a50dbbc9444a_942_248.jpg' && !empty($groups[$key]['header_path'])) {
				$groups[$key]['header_path'] = \Snake\Libs\Base\Utilities::getPictureUrl($groups[$key]['header_path'], '_o');
			}
			else {
				$groups[$key]['header_path'] = "";
			}
		}
		return $groups;
	}


	/**
	 * 取杂志社头部信息的方法
	 * @author : huazhulin
	 * @input : $groupId, $userId
	 * @return : array()
	 */
	public function getGroupHeader($groupId, $userId) {
		$groupBaseInfo = $this->getGroupInfo(array($groupId), array('group_id', 'name', 'count_member', 'header_path', 'description', 'show_poster'));
		if (empty($groupBaseInfo)) {
			return array();
		}
		$groupInfo = array();
		$groupUserHelper = new GroupUser();
		$groupTwitterHelper = new GroupTwitters();
		$gAdmins = $groupUserHelper->getGroupUserInfo($groupId, 1);
		$gMembers = $groupUserHelper->getGroupUserInfo($groupId, 0);
		$groupInfo['count_editor'] = count($gAdmins) + count($gMembers);
		$groupInfo['group_admins'] = array();
		if (!empty($gAdmins)) {
			$groupInfo['group_admins'] = array_slice($gAdmins, 0, 3);
		}
		$count = count($gAdmins);
		if (!empty($gMembers) && $count < 3) {
			$groupInfo['group_members'] = array_slice($gMembers, 0, 3 - $count);
		}
		else {
			$grouoInfo['members_info'] = $gAdmins;
			$groupInfo['group_members'] = array();
		}

		if (in_array($groupId, $this->otherGroups)) {
			$groupInfo['other_group'] = 1;
		}
		else {
			$groupInfo['other_group'] = 0;
		}

		if(in_array($groupId, $this->showCollectionGroups)) {
			$groupInfo['show_collection'] = 1;
		}
		else {
			$groupInfo['show_collection'] = 0;
		}
		if (in_array($groupId, $this->showHeaderCollectionGroups)) {
			$groupInfo['header_show_collection'] = 1;
		}
		else {
			$groupInfo['header_show_collection'] = 0;
		}
		$num = $groupTwitterHelper->getGroupsTwitterNumbers(array($groupId));
		if (!empty($num[$groupId]['num'])) {
			$groupBaseInfo[$groupId]['num'] = $num[$groupId]['num'];
		}
		else {
			$groupBaseInfo[$groupId]['num'] = 0;
		}
		$groupInfo['group_info'] = $groupBaseInfo[$groupId];
		$groupSquare = $this->getGroupSquareInfo(array($groupId), 0);
		if (isset($groupSquare[$groupId]['mixpic'])) {
			$groupInfo['group_info']['mixpic'] = $groupSquare[$groupId]['mixpic']; 
		}
		else {
			$groupInfo['group_info']['mixpic'] = \Snake\Libs\Base\Utilities::getPictureUrl('css/images/group/xxy1.gif', 'r');
		}
		if (!empty($userId)) {
			$groupInfo['role'] = $groupUserHelper->getUserRole($groupId, $userId, $gAdmins);
		}
		$groupInfo = $this->fillGroupType($groupInfo);
		$result = $this->checkGroupIfHighQuality(array($groupId));
		$groupInfo['high_quality'] = 0;
		$recommendHelper = new Recommend();
		//$result = $recommendHelper->groupIdOutOfDANPING($groupId);
		/*if (!empty($result)) {
			$groupInfo['high_quality'] = 1;
		}*/
		if (!empty($result)) {
			$recommendHelper = new Recommend();
			/*$result = $recommendHelper->groupIdOutOfDANPING($groupId);
			if (!empty($result[0])) {*/
			$groupInfo['high_quality'] = 1;
			//}
		}
		return $groupInfo;
	}

	private function fillGroupType($groupInfo) {
		if(empty($groupInfo)) {
			return array();
		}
		if ($groupInfo['other_group'] == 1) {
			$groupInfo['type_now'] = '加入我们';
			$groupInfo['type_be'] = '等待审核';
		}
		else {
			$groupInfo['type_now'] = '+ 加关注';
			$groupInfo['type_be'] = '已关注';
		}
		$groupInfo['type'] = 1;
		if (isset($groupInfo['role']) && $groupInfo['role'] == 0) {
			$groupInfo['type_be'] = $groupInfo['type_now'];
			$groupInfo['type_now'] = '已加入';
			$groupInfo['type_hover'] = '退出杂志';
		}
		else if ($groupInfo['role'] == 4){
			$groupInfo['type_be'] = $groupInfo['type_now'];
			$groupInfo['type_now'] = '等待审核';
			$groupInfo['type_hover'] = '取消申请';
		}
		else if ($groupInfo['role'] == 5) {
			$groupInfo['type_be'] = $groupInfo['type_now'];
			$groupInfo['type_now'] = '已关注';
			$groupInfo['type_hover'] = '取消关注';
		}
		else if ($groupInfo['role'] == 1) {
			$groupInfo['type_be'] = $groupInfo['type_now'];
			$groupInfo['type_now'] = '管理杂志';
			$groupInfo['type_hover'] = '';
			$groupInfo['type_be'] = '';
			$groupInfo['type'] = 2;
		}
		else {
			$groupInfo['type'] = 0;
			$groupInfo['type_hover'] = '';
			$groupInfo['type_be'] = $groupInfo['type_now'];
		}
		return $groupInfo;	
	}

	/**
	 *	取杂志社九宫格相关信息
	 *	@author : huazhulin
	 *	@params : $groupIds, $userId
	 *	@return : array()
	 */

	public function getGroupSquareInfo($groupIds = array(), $userId = 0) {
		if (empty($groupIds)) {
			return FALSE;	
		}
		$mClient = \Snake\Libs\Base\MultiClient::getClient(0);
		//$groupHelper = new Groups();
		$groupInfos = array();
		$groupUserHelper = new GroupUser();
		$groupTwitterHelper = new GroupTwitters();
        foreach ($groupIds AS $groupId) {
            $groupRequest = array();
            $groupRequest = array(
                'multi_func' => 'pop_group_twitter',
                'method' => 'GET',
                'group_id' => $groupId,
                'self_id' => 0,
            );
            $gRequest[] = $groupRequest;   
        }

        $groupPicUrls = $mClient->router($gRequest);
		$groupPic = array();
		$groupBaseInfo = $this->getGroupInfo($groupIds, array('group_id', 'name', 'count_member', 'header_path', 'admin_uid'));
		if (empty($groupBaseInfo)) {
			return array();
		}
		$num = $groupTwitterHelper->getGroupsTwitterNumbers($groupIds);
		foreach ($groupIds AS $key => $value) {
			$groupId = $groupIds[$key];
			if (empty($groupBaseInfo[$groupId])) {
				continue;
			}
			$groupBaseInfo[$groupId]['num'] = 0;
			if (!empty($num[$groupId]['num'])) {
				$groupBaseInfo[$groupId]['num'] = $num[$groupId]['num'];
			}
			$groupBaseInfo[$groupId]['is_follower'] = 0;
			$groupInfos[$groupId] = $groupBaseInfo[$groupId];
			if (!empty($groupPicUrls[$key]['pic'])) {
				$groupInfos[$groupId]['mixpic'] = $groupPicUrls[$key]['pic'];
			}
			else {
				$twitters = RedisGroupPosterOutbox::lRange($groupId, 0, 12);
				$tIds = $twitters;
				if (empty($twitters)) {
					$twitters = $groupTwitterHelper->getGroupTwitters(array($groupId), array('twitter_id', 'group_id', 'elite'), 0, 12);
					if (!empty($twitters)) {
						$tIds = array();
						$tIds = $twitters[$groupId];
						foreach ($twitters[$groupId] AS $k => $v) {
							$twitterIds[] = $twitters[$groupId][$k];
							//$tIds[] = $twitters[$groupId][$k]['twitter_id'];
						}
					}
				}
				else {
					if (!empty($twitterIds)) {
						$twitterIds = array_merge($twitterIds, $twitters);
					}
					else {
						$twitterIds = $twitters;
					}
				}
				$groupTwitters[$groupId] = $tIds;
				//$groupInfo[$groupId]['picture_url'] = 22;
			}
		}
		$twitterHelper = new Twitter();
		$twitterInfo = $twitterHelper->getPicturesByTids($twitterIds, "c");
		foreach ($groupInfos AS $key => $value) {
			if (empty($groupInfos[$key]['mixpic']) && !empty($groupInfos[$key])) {
				$picUrl = array();
				if (!empty($groupTwitters[$key])) {
					foreach ($groupTwitters[$key] AS $k => $v) {
						$twitterId = $groupTwitters[$key][$k];
						if (!empty($twitterInfo[$twitterId]['n_pic_file'])) {
							$picUrl[] = $twitterInfo[$twitterId]['n_pic_file'];
						}
					}
					$picUrl = array_slice($picUrl, 0, 9);
				}
				$groupInfos[$key]['picture_url'] = $picUrl;
			}
		}
		if (!empty($userId)) {
			$groupInfos = $this->fillSquare($groupInfos, $userId);
		}
		return $groupInfos;
	}

	
	/**
	 *	为杂志信息添加一列,是否是该杂志的关注者
	 *	@author : huazhulin
	 *	@params : $groupInfos, $userId
	 *	@return : array()
	 */
	public function fillSquare($groupInfos, $userId) {
		if (empty($groupInfos)) {
			return FALSE;
		}
		$groupUserHelper = new GroupUser();
		foreach ($groupInfos AS $key => $value) {
			$gIds[] = $groupInfos[$key]['group_id'];
		}
		$relation = $groupUserHelper->getGroupRelation($gIds, $userId);
		$userRole = array();
		if (is_array($relation[$userId])) {
			foreach ($relation[$userId] AS $key => $value) {
				$userRole[$relation[$userId][$key]['group_id']] = $relation[$userId][$key]['role'];
			}
		}
		foreach ($groupInfos AS $key => $value) {
			$groupInfos[$key]['is_editor'] = 0;
			if (!empty($userRole[$key]) && $userRole[$key] == 1) {
				$groupInfos[$key]['is_follower'] = 1;
				$groupInfos[$key]['is_editor'] = 1;
			}
			else if (isset($userRole[$key]) && $userRole[$key] != 8) {
				$groupInfos[$key]['is_follower'] = 1;
			}
			else if (empty($userRole[$key])) {
				$isFollower = $groupUserHelper->isGroupFollower($key, $userId, FALSE);
				if ($isFollower == TRUE) {
					$groupInfos[$key]['is_follower'] = 1;
				}
			}
		}
		return $groupInfos;
	}


	/**
	 *	更新杂志社相关信息
	 *	@author : huazhulin
	 *	@params : $groupIds(array)
	 *	@params : $fields(array),key为需要做出修改的列,value为对应的值,支持修改多个列
	 *	@return : bool
	 */
	public function updateGroupInfo($groupIds = array(), $fields = array(), $condition = array(), $userId = 0) {
		if (empty($groupIds) || empty($fields)) {
			return FALSE;
		}
		$groupCacheHelper = new GroupCache();
		foreach ($groupIds AS $groupId) {
			$group = array();
			$group['group_id'] = $groupId;
			$group['fields'] = $fields;
			if (empty($condition)) {
				$group['condition'] = array(
					'group_id' => $groupId
				);
			}
			else {
				$group['condition'] = $condition;
			}
			$groupCacheHelper->updateGroupCacheFromGroup($groupId, $userId);
			$result = OperationDB::operationDataBase($group, 'Group', 'update');
		}	
	}

	/**
	 *	创建杂志社	
	 *	@author : huazhulin
	 *	@params : $userId(int) 用户id
	 *	@params : $name(str) 杂志社名称
	 *	@params : $classifyNum(int) 分类名
	 *	@return : bool
	 */
	public function operationInsertGroup($userId, $name, $classifyNum) {
		if ($this->checkGroupNameExists($name)) {
			return FALSE;
		}

		$countMember = RedisUserFans::getFansNumber($userId) + 1;
		$groupInfo = array(
			'admin_uid' => $userId,
			'name' => $name,
			'created' => time(),
			'description' => '爱美丽的杂志,大家一块来玩吧',
			'group_attr' => 1,
			'show_poster' => 1,
			'header_path' => 'glogo/_o/77/62/5ca16a2ba61b5933a50dbbc9444a_942_248.jpg',
			'count_member' => $countMember,
			'logo_path' => 'glogo/a/24/3a/029fd92ab2a12e07e04a57460a69_180_180.jpg'
		);
		$group = array(
			'fields' => $groupInfo,
			'insert' => 1
		);
		$role = 1;
		//$groupId = $this->operationGroup($group);
		$groupId = OperationDB::operationDataBase($group, 'Group', 'insert');
		$groupInfo['group_id'] = $groupId;
		$groupInfo['logo_path'] = 'glogo/a/24/3a/029fd92ab2a12e07e04a57460a69_180_180.jpg';
		$groupCacheHelper = new GroupCache();
		$groupCacheHelper->setGroupInfoCache($groupInfo);
		$groupUserHelper = new GroupUser();
		$groupUserHelper->insertGroupUser($userId, $groupId, $role);
		//$groupUserHelper->updateUserGroupFollower($user, $groupId, $userId);
		if (!empty($classifyNum)) {
			$group = array(
				'fields' => 
					array(
						'group_id' => $groupId,
						'classify_num' => $classifyNum
					),
				'insert' => 1
			);
			OperationDB::operationDataBase($group, 'GroupClassify', 'insert');
			//$this->operationClassifyGroup($group);
		}
		return $groupId;
	}

	public function deleteGroup($groupId, $userId) {
		if (empty($groupId) || empty($userId)) {
			return FALSE;
		}

		$groupTwitterHelper = new GroupTwitters();
		$groupUserHelper = new GroupUser();
		$groupCacheHelper = new GroupCache();
		$groupMainCHelper = new GroupMainCatalog();
		$groupCHelper = new GroupCatalog();
		$groupTopicHelper = new GroupTopics();
		$recommendHelper = new Recommend();
		$group = array(
			'condition' => array(
				'group_id' => $groupId,
			)
		);
		$result = $groupUserHelper->getGroupRelation(array($groupId), $userId, array('group_id', 'role'));
		if (empty($result) || ($result[0]['role'] != 1 && !in_array($userId, $this->superUsers))) {
			return FALSE;
		}
		$fields = array(
			'group_attr' => 0,
		);
		$updateInfo = array(
			'fields' => $fields,
			'condition' => array(
				'group_id' => $groupId
			)
		);
		//$this->operationDeleteGroup($group);
		//$this->deleteClassifyGroup($group);
		//$this->deleteHighQualityGroup($group);
		$result = OperationDB::operationDataBase($updateInfo, 'Group', 'update');
		OperationDB::operationDataBase($group, 'GroupClassify', 'delete');
		OperationDB::operationDataBase($group, 'GroupHighQuality', 'delete');
		RedisGroupPosterOutbox::del($groupId);
		$groupMainCHelper->deleteGroup($groupId);
		$groupCHelper->deleteGroup($groupId);
		$groupTwitterHelper->deleteGroupTwitters($groupId);
		$groupUserHelper->operationQuit(0, $groupId);
		$groupTopicHelper->deleteTopic($groupId);
		RedisUserGroupUnFollower::del($groupId);
		$groupCacheHelper->updateGroupCacheFromGroup($groupId, $userId);
		$recommendHelper->deleteTopAttrByGid($groupId);
		$recommendHelper->deleteAttrByGid($groupId);
		return $result;
	}

	/**
	 *	检查该杂志名称是否存在
	 *	@author : huazhulin
	 *	@params : $name(string)
	 *	@params : $fields(array())
	 *	@return : bool
	 */
	public function checkGroupNameExists($name, $fields = array('group_id'), $groupId = 0) {
		$identityObject = new IdentityObject();
		$identityObject->field('name')->eq($name);
		$identityObject->col($fields);

		$domainObjectAssembler = new DomainObjectAssembler(GroupPersistenceFactory::getFactory('\Snake\Package\Group\GroupPersistenceFactory'));
		$groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
		while ($groupCollection->valid()) {
            $groupObj = $groupCollection->next();   
            $groups = $groupObj->getGroup();
        }
		if (isset($groupId) && $groups['group_id'] == $groupId) {
			return FALSE;	
		}
		if (!empty($groups)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *  从CMS中取右侧杂志社	
	 *	@author : huazhulin
	 *	@return : array 
	 */
	public function getCMSGroupSideBar() {
        $params = array();
        $params['limit'] = 5;
        $params['page_type'] = 22; 
        $params['date_type'] = 2;
        $params['orderby'] = "sortno ASC";
        $dataIds = CmsIndexWelcome::getCmsData($params, 'distinct data_id');
		$groupIds = array();
		foreach ($dataIds AS $key => $value) {
			$groupIds[] = $dataIds[$key]['data_id'];
		}
		return $groupIds;
	}

	public function getPopularGroupForGuang($userId, $magFavor) {
		/*if ($magFavor === 't') {
			$groupInfo = $this->getDesignateGroup($userId);
		}
		else {*/
			$groupIds = $this->getCMSGroupSideBar();
			$groupInfo = $this->getGroupSquareInfo($groupIds, $userId);
		//}
		$groupInfo = array_values($groupInfo);
		return $groupInfo;
	}

	public function getPopularGroupForAttr($wordId, $userId, $magFavor) {
		if ($magFavor === 't') {
			$reGroupIds = array(15816321, 15671983, 15670190);
		}
		else {
			$recommendHelper = new Recommend();		
			$reGroupIdsTmp = $recommendHelper->getReGroupByAid($wordId);
			if (empty($reGroupIdsTmp)) {
				return array();
			}
			$reGroupIdsTmp = array_slice($reGroupIdsTmp, 0, 3);
			$reGroupIds = array();
			foreach ($reGroupIdsTmp as $group) {
				$reGroupIds[] = $group['group_id'];
			}
		}
		$groups = $this->getGroupSquareInfo($reGroupIds, $userId);
		if (!empty($groups) && is_array($groups)) {
			$groups = array_values($groups);
		}
		else {
			$groups = array(); 
		}
		return $groups;

	}

	public function getDesignateGroup($userId) {
		$groupIds = array(14067, 12065305, 19108, 27794);
		$groupInfo = $this->getGroupSquareInfo($groupIds, $userId);
		$keyWZ = 'css/images/group/xiaowanzi1.jpg';
		$keyDD =  'css/images/group/dingdang1.jpg';
		$keyHK =  'css/images/group/hellokitty1.jpg';
		$keyRB =  'css/images/group/bear1.jpg';
		$groupInfo['14067']['mixpic'] = \Snake\Libs\Base\Utilities::getPictureUrl($keyHK, 'r'); ;
		$groupInfo['14067']['group_id'] = 14067;
		$groupInfo['12065305']['mixpic'] = \Snake\Libs\Base\Utilities::getPictureUrl($keyRB, 'r');
		$groupInfo['12065305']['group_id'] = 12065305;
		$groupInfo['19108']['mixpic'] = \Snake\Libs\Base\Utilities::getPictureUrl($keyDD, 'r');
		$groupInfo['19108']['group_id'] = 19108;
		$groupInfo['27794']['mixpic'] = \Snake\Libs\Base\Utilities::getPictureUrl($keyWZ, 'r');
		$groupInfo['27794']['group_id'] = 27794;
		return $groupInfo;
	}

	/**
	 *  从CMS中取分类杂志社	
	 *	@author : huazhulin
	 *  @param : $typeName(string)
	 *	@param : $pageType(int)
	 *	@param : $num(int)
	 *	@return : array 
	 */
	public function getKindsOfGroups($typeName = "欧美", $pageType = 52, $num = 1) {
        $params = array();
        $params['limit'] = $num*10;
        $params['page_type'] = $pageType; 
        $params['date_type'] = 2;
        $params['orderby'] = "sortno ASC";
        $dataIds = CmsIndexWelcome::getCmsData($params, 'distinct data_id, twitter_type, id');

        $params = array();  
        $params['type_name'] = $typeName;
		$params['page_type'] = $pageType;
        $data = CmsIndexType::getCmsData($params, "*");
		$ids = array();
		foreach ($dataIds AS $key => $value) {
			if ($data[0]['id'] == $dataIds[$key]['twitter_type']) {
				$ids[] = $dataIds[$key]['data_id'];
			}
		}
		return $ids;
	}

	public function getGroupsNinePictures($groupIds) {
		if (empty($groupIds) || !is_array($groupIds)) {
			return FALSE;
		}
		$groupHelper = new GroupTwitters();
		$groupTwitters = $groupHelper->getGroupTwitters($groupIds, array('twitter_id', 'group_id', 'elite'), 0, 20);
		foreach ($groupTwitters AS $twitters) {
			if (!empty($tIds)) {
				$tIds = array_merge($twitters, $tIds);
			}
			else {
				$tIds = $twitters;
			}
		}
		$twitterHelper = new Twitter();
		$twitterInfo = $twitterHelper->getPicturesByTids($tIds, "c");
		$groupInfo = array();
		foreach ($groupTwitters AS $k => $v) {
			foreach ($groupTwitters[$k] AS $twitterId) {
				$groupInfo[$k][] = $twitterInfo[$twitterId]['n_pic_file'];
				if (count($groupInfo[$k]) == 9) {
					break;
				}
			}
		}
		return $groupInfo;
	}

	public function checkGroupIfHighQuality(array $groupIds, $status = 1, $fields = array('group_id')) {
		$Params = array();
		$paramsWhere = array(
			array(
				'operation' => 'in',
				'key' => 'group_id',
				'value' => $groupIds
				),
			array(
				'operation' => 'eq',
				'key' => 'status',
				'value' => $status
				)
			);
		$parameters = array(
			'where' => $paramsWhere,
			'fields' => $fields
		);
		$hashKey = "";
		$result = OperationDB::selectDataBase($parameters, 'GroupHighQuality', $hashKey);
		return $result;
	}

	public function getAdminFriends($userId, $groupId, $frame = 0, $limit = 20) {
		$cacheHelper = Memcache::instance();
		$cacheKey = "ADMIN_FRIENDS:" . $userId . ":" . $groupId;
		$result = $cacheHelper->get($cacheKey);

		if (!empty($friendIds)) {
			$offset = $frame * 20;
			$result = array_slice($result, $offset, $limit);
			return $result;
		}

		$userRelationHelper = new UserRelation();
		$groupUserHelper = new GroupUser();
		$userHelper = new User();
		$friendIds = $userRelationHelper->getMutualFollow($userId);
		if (empty($friendIds)) {
			return array();
		}
		$editorIds = array();
		$editorIds = $groupUserHelper->getGroupUsersByRole(array($groupId), array('user_id', 'role', 'created'), array(0, 1), 0, 500);
		$editorIds = $editorIds[$groupId];
		$eIds = array_keys($editorIds);
		$friendIds = array_diff($friendIds, $eIds);
		$result = array();
		if (!empty($friendIds)) {
			$invitationIds = RedisGroupInvitation::getInvitationId($groupId);
			$friendIds = array_diff($friendIds, $invitationIds);
			if (empty($friendIds)) {
				return array();
			}
			$result = $userHelper->getUserInfos($friendIds);
			$cacheHelper->set($cacheKey, $result, 600);
			$result = array_slice($result, $offset, $limit);
		}
		return $result;
	}

	public function getShareRightGroups($twitterId,  $userId, $authorId, $number = 3, $useCache = TRUE) {
		$role = array(0,1);
		$limit = 100;
		$offset = 0;
		if ($useCache == TRUE) {
			$cacheHelper = Memcache::instance();
			$cacheKey = "Groups:RightGroupIds:" . $twitterId;
			$groupIds = $cacheHelper->get($cacheKey);
		}
		if (empty($groupIds)) {
			$groupIds = $this->_rightGroupIds($twitterId, $role, $authorId, $limit, $number);
		}
		if (empty($groupIds)) {
			return array();
		}
		if ($useCache == TRUE) {
			$cacheHelper->set($cacheKey, $groupIds, 600);
		}
		$groupInfos = $this->getGroupSquareInfo($groupIds, $userId);
		$groupInfos = array_values($groupInfos);

		return $groupInfos;
	}

	private function _rightGroupIds($twitterId, $role, $authorId, $limit, $wantNum) {
		$start = 0;
		$groupTwitterHelper = new GroupTwitters();
		$groupUserHelper = new GroupUser();
		$groupId = $groupTwitterHelper->getGroupTwitter(array($twitterId), array('twitter_id', 'group_id'));
		$groupId = $groupId[$twitterId]['group_id'];
		$userIds = $groupUserHelper->getGroupUsersByRole(array($groupId), array('user_id', 'group_id'), $role);
		$adminIds  = array_keys($userIds[$groupId]);
		if (empty($adminIds)) {
			$adminIds = array($authorId);
		}
		$groupIds = $groupUserHelper->getUserGroupsByRole($adminIds, $role, array('group_id', 'role', 'created'), "user_id DESC", $start, $limit);
		$gIds = \Snake\Libs\Base\Utilities::DataToArray($groupIds, 'group_id');
		$gIds = array_unique($gIds);
		$gIds = array_values($gIds);
		if (empty($gIds)) {
			return array();
		}
		$sortGroupIds = $groupUserHelper->sortGroupsByGroupRank($gIds,array("group_id", "last_twitter_number"), "last_twitter_number DESC", "group_id" );
		unset($sortGroupIds[$groupId]);
		if (empty($sortGroupIds)) {
			return array();
		}

		foreach ($sortGroupIds AS $key => $value) {
			if (empty($sortGroupIds[$key][0]['last_twitter_number']) || $sortGroupIds[$key][0]['last_twitter_number'] < 9) {
				unset($sortGroupIds[$key]);
			}
		}
		if (!empty($sortGroupIds)) {
			$result = array_keys($sortGroupIds);
			if (!empty($result)) {
				shuffle($result);
				$result = array_slice($result, 0 , $wantNum);
			}
		}
		return $result;
	}

	public function getAllGroups() {

	}



}
