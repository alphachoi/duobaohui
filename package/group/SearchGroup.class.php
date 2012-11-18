<?php
namespace Snake\Package\Group;

Use \Snake\Package\Search\SearchGroupExpr;
Use \Snake\Package\Search\SearchObject;
class SearchGroup {
	
	private $searchKey = '';
	private $offset = 0;
	private $pageSize = 20;
	private $searchObj = NULL;
	private $index = 'topic_group';
	private $searchResult = NULL;
	private $searchUseCache = FALSE;

	function __construct() {
		$this->searchObj = new SearchObject();
		$this->searchObj->setUseCache(FALSE);
	}
	
	public function setSearchKey($searchKey) {
		$this->searchKey = $searchKey;
		return TRUE;
	}


	public function setOffset($offset, $pageSize) {
		$this->offset = $offset;
		$this->pageSize = $pageSize;
		return TRUE;
	}
	
	public function setSearchResult($orderBy = '') {
		if ($orderBy === 'weight') {
			$searchExpr = $this->_getSearchExprForWeight();
			$this->searchObj->setMatchMode(SPH_MATCH_PHRASE);
			$this->searchObj->setSortMode(SPH_MATCH_EXTENDED, $searchExpr);
		}
		else {	
			$searchExpr = $this->_getSearchExpr();
			$this->searchObj->setMatchMode(SPH_MATCH_EXTENDED);
			$this->searchObj->setSortMode(SPH_SORT_EXPR, $searchExpr);
		}
		$this->searchObj->setLimit($this->offset, $this->pageSize);
		$this->searchObj->setIndex($this->index);
		$this->searchObj->search($this->searchKey);
		$this->searchResult = $this->searchObj->getSearchResult();
		return TRUE;
	}

	public function getSearchResult() {
		return $this->searchResult;
	}

	private function _getSearchExpr() {
		$searchExprHelper = new SearchGroupExpr();
		$searchExprHelper->getExpr();
		$searchExpr = $searchExprHelper->getValue();
		return $searchExpr;
	}

	private function _getSearchExprForWeight() {
		$searchExpr = '@weight DESC, count DESC';
		return $searchExpr;
	}

}
