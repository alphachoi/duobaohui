<?php
namespace Snake\Package\User;

USE \Snake\Package\Search\SearchObject AS SearchObject;
USE \Snake\Libs\Base\Utilities AS Utilities;

class UserSearch {

	private $uids;
	private $totalNum;
	private $wordName = '';
	private $offset = 0;
	private $limit = 20;
	private $searchObject = NULL;
	const index = 'user_nickname';

	private $searchResult = array();

	public function __construct() {
		$this->searchObject = new SearchObject();
	}

	public function setOffset($offset) {
		$this->offset = $offset;
	}

	public function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 * 查询词
	 */
	public function setWordName($wordName) {
		$this->wordName = $wordName;
	}

	public function dataProcess() {
	    if (!$this->setUidsAndTotalNum()) {
            return FALSE;
        }
        return TRUE;	
	}

	private function setUidsAndTotalNum() {
		$sortBy = $this->getSortBy();
		//精确搜索
		$retEx = $this->_searchUserEx($this->offset, $this->limit, $this->wordName, self::index);
		$retExCount = 0;
		$retExUids = array();
		if (!empty($retEx['matches'])) {
			$retExCount = count($retEx['matches']);	
			$retExUids = Utilities::DataToArray($retEx['matches'], 'id');
		}
		//模糊搜索
		$ret = $this->_searchForResult($this->offset, $this->limit, $sortBy, $this->wordName, self::index);
		$this->uids = Utilities::DataToArray($ret['matches'], 'id');
		$this->uids = array_flip(array_flip(array_merge($retExUids, $this->uids)));
		$this->totalNum = $ret['total'] < $ret['total_found'] ? $ret['total'] : $ret['total_found'];
		return TRUE;
	}

	private function getSortBy() {
		$searchTime = time();
		$sortBy = "LOG10(login_times) * IF(avatar_a == 1,1,0.5) * (5-level)/5*IF(is_business == 10,1.1,1)*IF(is_business == 1,1.05,1) + ( $searchTime - ctime)/(3600 * 24 * 365 *4)";
		return $sortBy;
	}

	private function _searchForResult($offset, $limit, $sortBy, $wordName, $index) {
        $this->searchObject->setLimit($offset, $limit);
		$this->searchObject->SetFilter('is_actived', array(0, 1, 2));
        $this->searchObject->setMatchMode(SPH_MATCH_PHRASE);
		$this->searchObject->setSortMode(SPH_SORT_EXPR, $sortBy);
		$this->searchObject->setIndex($index);
		$this->searchObject->search($wordName);
		$searchResult = $this->searchObject->getSearchResult();
		if (empty($searchResult['matches'])) { 
			$searchResult = array();
		}
		return $searchResult;
	}

    public function getUids() {
        return $this->uids; 
    }   

    public function getTotalNum() {
        return $this->totalNum;
    }  
	
	private function _searchUserEx($offset, $limit, $wordName, $index) {
		$this->searchObject->setLimit($offset, $limit);
		$this->searchObject->SetFilter('is_actived', array(0, 1, 2));
        $this->searchObject->setMatchMode(SPH_MATCH_PHRASE);
        $this->searchObject->setSortMode(SPH_SORT_EXPR, "@weight");
		$this->searchObject->setIndex($index);
		$wordName = "^".$wordName."$";
		$this->searchObject->search($wordName);
		$searchResult = $this->searchObject->getSearchResult();
		if (empty($searchResult['matches'])) {
			$searchResult = array();
		}
		return $searchResult;
    } 
}
