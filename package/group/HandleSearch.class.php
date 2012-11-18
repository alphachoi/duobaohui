<?php
namespace Snake\Package\Group;

Use \Snake\Package\Group\SearchGroup;

class HandleSearch {
	
	private $searchGroupObj = NULL;	
	private $searchResult = NULL;

	function __construct() {
		$this->searchGroupObj = new SearchGroup();
	}
	
	private function _initSearchParameters($searchKey, $offset = 0, $limit = 20) {
		if (!isset($searchKey) || empty($limit) || is_array($searchKey)) {
			return FALSE;
		}
		$this->searchGroupObj->setSearchKey($searchKey);
		$this->searchGroupObj->setOffset($offset, $limit);
		return TRUE;
		$this->searchGroupObj->setSearchResult();
	}

	private function _getSearchResult($orderBy = '') {
		$this->searchGroupObj->setSearchResult($orderBy);
		$this->searchResult = $this->searchGroupObj->getSearchResult();
		return $this->searchResult;
	}

	private function _searchGroupNormal($searchKey, $offset, $limit, $orderBy = '') {
		if(!$this->_initSearchParameters($searchKey, $offset, $limit)) {
			return array();
		}
		$result = $this->_getSearchResult($orderBy);
		return $result;
	}

	public function handleSearchGroup($searchKey, $userId = 0, $offset = 0, $limit = 20, $getNum = 0) {
		$searchKeyEx = "^$searchKey$";
		$resultEx = $this->_searchGroupNormal($searchKeyEx, $offset, $limit, 'weight');
		$groupIds = array();
		$groupIds = \Snake\Libs\Base\Utilities::DataToArray($resultEx['matches'], 'id');
		$totalNum = min($resultEx['total'], $resultEx['total_found']);
		if(count($groupIds) < $limit || $getNum == 1) {
			$rest = $limit - count($groupIds);
			if ($getNum == 1) {
				$offset = 0;
				$rest = 1;
			}
			$result = $this->_searchGroupNormal($searchKey, $offset, $rest);
			$groupIdsAdd = \Snake\Libs\Base\Utilities::DataToArray($result['matches'], 'id');
			$groupIds = array_merge($groupIds, $groupIdsAdd);
			//$totalNum = min($resultEx['total'], $resultEx['total_found']) + min($result['total'], $result['total_found']);
			$totalNum = min($result['total'], $result['total_found']);
			if ($getNum == 1) {
				$totalNum = max($result['total'], $result['total_found']);
			}
		}
		if (!empty($getNum)) {
			$showNum = $totalNum;
			$total = $totalNum;
			if (empty($showNum)) {
				$showNum = 0;
			}
			if (empty($total)) {
				$total = 0;
			}
			if ($total > 12000) {
				$total = 12000;
			}
			$num = array(
				'totalNum' => $total,
				'showNum' => $showNum
			);
			return $num;
		}

		if (empty($groupIds)) {
			//$groupIds = array(3,4,5,6,7,8,9);
			//$totalNum = 6;
			$searchResult['magazine'] = array(
					'gInfo' => array(),
					'totalNum' => 0
			);  
			return $searchResult;
		} 
		$groupInfo = $this->_getGroupInfo($groupIds, $userId);
		$groupInfo = array_values($groupInfo);
		$searchResult = array();
		$searchResult['magazine'] = array(
			'gInfo' => $groupInfo,
			'totalNum' => $totalNum
		);
		return $searchResult;
	}

	private function _getGroupInfo($groupIds, $userId = 0) {
		if (empty($groupIds) || !is_array($groupIds)) {
			return FALSE;
		}
		$groupHelper = new Groups();
		$groupInfo = $groupHelper->getGroupSquareInfo($groupIds, $userId);
		return $groupInfo;
	}
	//得到搜索到的杂志id

	public function searchGroupIds($searchKey, $userId = 0, $offset = 0, $limit = 20) {
        $searchKeyEx = "^$searchKey$";
        $resultEx = $this->_searchGroupNormal($searchKeyEx, $offset, $limit, 'weight');
        $groupIds = array();
        $groupIds = \Snake\Libs\Base\Utilities::DataToArray($resultEx['matches'], 'id');
        $num = count($groupIds);
        if ($num < $limit) {
            $rest = $limit - $num;
            $result = $this->_searchGroupNormal($searchKey, $offset, $rest);
            $groupIdsAdd = array();
            $groupIdsAdd = \Snake\Libs\Base\Utilities::DataToArray($result['matches'], 'id');
            $groupIds = array_merge($groupIds, $groupIdsAdd);
        }
        return $groupIds;
    }   
}

