<?php
namespace Snake\Package\Group;


/**
 *	取杂志社列表相关信息的package
 *	@author : huazhulin
 *	@since : 2012-11-14
 *	@mail : huazhulin@meilishuo.com
 *	@version : 1.0
 */
USE \Snake\Package\Group\GroupTwitters;
USE \Snake\Package\Group\GroupCache;
USE \Snake\Package\Listfactory\ListWall;

class GroupList {

	private $groupId = 0;
	private $userId = 0;
	private $listInfo = array();
	private $elite = array(0,1,2);
	private $orderBy = "twitter_id DESC";
	private $hashKey = "";
	private $showType = 0;
	private $fields = array('twitter_id', 'group_id', 'elite', 'created'); 
	private $havePicture = 1;

	public function __construct($groupId) {
		if (empty($groupId)) {
			return FALSE;
		}
		$this->groupId = $groupId;
		return TRUE;
	}

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function setElite($elite) {
		$this->elite = $elite;
	}
	
	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
	}

	public function getGroupList($userId, $elite, $offset, $limit) {
		$this->setUserId($userId);
		$this->setElite($elite);
		$result = $this->getTwitterIds($offset, $limit);
		$tIds = \Snake\Libs\Base\Utilities::DataToArray($result, 'twitter_id');
		$this->setListInfo($tIds);
		return $this->listInfo;
	}

	public function getTwitterIds($offset, $limit) {
		$groupTwitterHelper = new GroupTwitters();
		$groupCacheHelper = new GroupCache();
		$twitterIds = $groupTwitterHelper->getGroupTwittersByGroupIdsNoCache(array($this->groupId), $this->fields, $offset, $limit, $this->orderBy, $this->hashKey, $this->showType, $this->havePicture, $this->elite);
		$cacheTwitters = $groupCacheHelper->getGroupTwitterCache($this->groupId);
		if (!empty($cacheTwitters)) {
			$twitterIds = array_merge($cacheTwitters, $twitterIds);
		}
		$twitterIds = array_slice($twitterIds, $offset, $limit);
		return $twitterIds;
	}
	
	public function setListInfo($tIds) {
		$listHelper = new ListWall();
		$listHelper->setTids($tIds);
		$listHelper->setUid($this->userId);
		$listHelper->setIsGroup(1);
		$result = $listHelper->getList();
		$this->listInfo = $result;
		return TRUE;
	}


}
