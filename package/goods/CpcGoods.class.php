<?php
namespace Snake\Package\Goods;

Use Snake\Package\Search\SearchObject;
Use Snake\Package\Search\SearchUserExpr;
Use Snake\Package\Search\SearchCpcBusinessExpr;
Use Snake\Package\Search\SearchBusinessExpr;

Use Snake\Package\Search\CataExpr;
Use Snake\Package\Search\BracketExpr;
Use Snake\Package\Search\BusExpr;
Use Snake\Package\Search\UserExpr;
Use Snake\Package\Search\MainExpr;
Use Snake\Package\Search\CpcBusExpr;
Use Snake\Package\Search\CpcUserTestExpr;
Use Snake\Package\Search\CpcBusTestExpr;




class CpcGoods {
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
		//$this->SearchObject->setUseCache(FALSE);
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
		$exprBuilder = new CataExpr(new BracketExpr(new CpcBusExpr(new CpcUserTestExpr(new MainExpr(), ""), "+"), ""), "*");
		$exprBuilder->setSearchKey($keywords);
		$searchExpr = $exprBuilder->getExpr();
		return $searchExpr;

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
		return $this->getSearchResult($this->requestObject->getWordName(), $this->index);
	}	

}













