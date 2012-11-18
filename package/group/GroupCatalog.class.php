<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\Helper\DBGroupCmsHelper AS DBGroupCmsHelper;
Use \Snake\Package\Group\Helper\DBGroupCatalogHelper AS DBGroupCatalogHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;

class GroupCatalog {
	
	private $pageInfo = array();
    private $catalogId = 0;
	private $groupIds = array();

	public function __construct($catalogId = 88) {
		$this->catalogId = $catalogId;
		if ($this->catalogId == 88) {
			$this->catalogId = 99;
		}
	}

	public function getGroupIdsByCatalogId($getFrom, $getLimit) {
		$mem = Memcache::instance();
		$cacheKey = "GROUP_CATALOGID:" . $this->catalogId;
		$groupIds = $mem->get($cacheKey);
		if (empty($groupIds)) {
			$sqlData = array(
					'_class_id' => $this->catalogId
					//'order_by'  => "group_id DESC",
					//'_start'    => $getFrom,
					//'_limit'	=> $getLimit
			);
			$sqlComm = "SELECT group_id FROM t_dolphin_group_class_map 
				WHERE class_id = :_class_id 
				ORDER BY group_id DESC";
			$gId = DBGroupCatalogHelper::getConn()->read($sqlComm, $sqlData);
			$groupIds = array();
			foreach ($gId AS $key => $value) {
				$groupIds[] = $gId[$key]['group_id'];
			}
			$groupIds = $this->sortGroupsByGroupRank($groupIds);
			$mem->set($cacheKey, $groupIds, 480);
		}
		if (!empty($groupIds) && !empty($getLimit)) {
				
			$groupIds = array_slice($groupIds, $getFrom, $getLimit + 3);
		}
		$groupInfo['link'] = $classId;
        $gIds = array();
		if (empty($groupIds)) {
			$this->view = array();
			return ;
		}
        foreach ($groupIds AS $groupId) {
			$gIds[]['id'] = $groupId; 
		}
        $this->groupIds = $gIds;
	}

	public function getAllGroups() {
		$sqlComm = "SELECT group_id FROM t_dolphin_group_class_map 
			ORDER BY group_id DESC";
		$gId = DBGroupCatalogHelper::getConn()->read($sqlComm, array());
		return $gId;
	}

	public function getCatalogPage($page) {
		$sqlData = array(
			'_class_id' => $this->catalogId
			);
		$sql = "SELECT count(*) as num FROM t_dolphin_group_class_map WHERE class_id = :_class_id";
		$num = DBGroupCmsHelper::getConn()->read($sql, $sqlData);
		$pageInfo = array(
			'page_size' => 40, 
			'total_num' => $num[0]['num'],
			'url'		=> BASE_URL . 'group?tab=' . $this->catalogId . '&page=',
			'current_page' => $page
			);
		$this->pageInfo = $pageInfo;
	}

    public function sortGroupsByGroupRank($groupIds, $fields = array('group_id'), $orderBy = "last_twitter_number DESC") {
        if (empty($groupIds)) {
            return FALSE;
        }
        $identityObject = new IdentityObject(); 
        $identityObject->field('group_id')->in($groupIds)->field('last_twitter_number')->gt(100)->orderby($orderBy);
        $identityObject->col($fields);
        $domainObjectAssembler = new DomainObjectAssembler(GroupRankPersistenceFactory::getFactory('\Snake\Package\Group\GroupRankPersistenceFactory'));
        $groupCollection = $domainObjectAssembler->mysqlFind($identityObject);
        $groups = array();
        while ($groupCollection->valid()) {
            $groupObj = $groupCollection->next();
            $groups[] = $groupObj->getGroupId();
        }
        if (!empty($groups)) {
			shuffle($groups);
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

	public function deleteGroup($groupId) {
		$sql = "DELETE FROM t_dolphin_group_class_map WHERE group_id = {$groupId}";
		DBGroupCmsHelper::getConn()->write($sql, array());
		return TRUE;
	}

    public function getGroupIds() {
        return $this->groupIds;
    }

	public function getPage() {
		return $this->pageInfo;
	}
	public function save() {
		//TODO
	}
}
