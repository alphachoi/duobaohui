<?php
namespace Snake\Package\Goods;

class CatalogSearchTest {

	private $requestObject = NULL;
	private $searchObjectNormal = NULL;
	private $searchObjectCpc = NULL;
	const daysFilter = 200;
	
	private $CpcGoods = NULL;
	private $searchKey = '';
	private $searchString = '';
	private $SearchObject = NULL;
	private $requestObject = NULL;
	private $index = "goods_id_business";
	const daysFilter = 200;
	const max = 12000;

	public function __construct() {
		$this->setSearchObject();
		$this->setRequestObject();
		$this->initializeSearch();
	}

	private function setRequestObject() {
		$registry = Registry::instance();
		$this->requestObject = $registry->getRequest();
		return TRUE;
	}

	private function setSearchObject() {
		$this->SearchObject = new SearchObject('cpc');
		return TRUE;
	}

	private function initializeSearch() {
		$endTime = time();
		$beginTime = $endTime - self::daysFilter * 86400;
		$this->SearchObject->setFilterRange('goods_author_ctime', $beginTime, $endTime);
	}	

	private function composeExpr($searchKey) {
		$catExpr = $this->SearchObject->getCataExpr($searchKey);
		$searchUserExpr = new SearchUserExpr();
		$userExpr = $searchUserExpr->getExpr();

		$searchCpcBusinessExpr = new SearchCpcBusinessExpr();
		$cpcBusExpr = $searchCpcBusinessExpr->getExpr();

		$expr = "$catExpr * ($userExpr + $cpcBusExpr)";
		return $expr;
	} 

	private function getSearchResult($searchKey, $index = "goods_id_business") {
		if (empty($searchKey )){
			return FALSE;
		}
		$cpcMax = 12000;	
		$cpcExpr = $this->composeExpr($searchKey);
		$this->SearchObject->setIndex($index);
		$this->SearchObject->setLimit(0, $cpcMax - 1, $cpcMax);
		$this->SearchObject->setSortMode(SPH_SORT_EXPR, $cpcExpr);
		$this->SearchObject->search($searchKey);
		$cpcSearchResult = $this->SearchObject->getSearchResult();
		return $cpcSearchResult;
	}

	public function setVerifyFilter($verify = array(1,2,5)) {
		return $this->SearchObject->setFilter('verify_stat', $verify);
	}


	private function initConditionForSearch() {
		$this->searchKey = $this->requestObject->getWordName();
		$this->searchString = $this->getSearchString($this->searchKey);
		return TRUE;
	}

	public function search() {
		//$this->initConditionForSearch();
		return $this->getSearchResult($this->requestObject->getWordName(), $this->index);
	}	


}
