<?php
namespace Snake\Package\Goods;

Use Snake\Package\Search\SearchObject;
Use Snake\Package\Search\SearchUserExpr;
Use Snake\Package\Search\SearchBusinessExpr;
Use Snake\Package\Search\SearchCpcBusinessExpr;
Use Snake\Package\Search\UserExpr;
Use Snake\Package\Search\MainExpr;
Use Snake\Package\Search\BracketExpr;
Use Snake\Package\Search\BusExpr;
Use Snake\Package\Search\CpcBusExpr;
Use Snake\Package\Search\CpcBusTestExpr;
Use Snake\Package\Search\CataExpr;

class SearchGoods {

	protected $searchKey = '';
	protected $searchString = '';
	protected $offset = 0;
	protected $pageSize = 20;
	protected $SearchObject = NULL;
	protected $wordName = '';
	protected $searchCacheSwitch = TRUE;
	const daysFilter = 200;
	const index = 'goods_id_dist';
	const cpcIndex = 'goods_id_business';

	function __construct() {
		$this->SearchObject = new SearchObject();
		$endTime = \Snake\Libs\Base\Utilities::timeByTenMin();
		$beginTime = $endTime - self::daysFilter * 86400;
		$this->SearchObject->setFilterRange('goods_author_ctime', $beginTime, $endTime);
		$this->SearchObject->setUseCache($this->searchCacheSwitch);
	}

	protected function initConditionForSearch() {
		$this->SearchObject->setLimit($this->offset * $this->pageSize, $this->pageSize);
	}

	public function setWordName($wordName) {
		if (empty($wordName)) {
			return FALSE;
		}
		$this->wordName = $wordName;
		return TRUE;
	}

	public function setOffset($offset) {
		$this->offset = (int)$offset;	
		return TRUE;
	}

	public function setPageSize($pageSize) {
		$this->pageSize = (int)$pageSize;	
		return TRUE;
	}

	public function getSearchString($wordName) {
		if (empty($wordName)) {
			return FALSE;
		}
		$params = array();
		$params['word_name'] = $wordName;
		$params['isuse'] = 1;
		$wordInfo = AttrWords::getWordInfo($params, "/*searchGoods-zx*/word_id,word_name");
		if (!empty($wordInfo)) {
			$searchKeyArr = AttrWords::getSearchString($wordInfo[0]['word_id'], $wordName);
		}
		else{
			$searchKeyArr = array("{$wordName}");	
		}
		$searchString = "(" . implode(")|(", $searchKeyArr) . ")";
		$this->searchString = $searchString;
		return $searchString;
	}

	protected function composeExpr($searchKey, $cpcSearch = FALSE) {
		/*$catExpr = $this->SearchObject->getCataExpr($searchKey);
		$searchUserExpr = new SearchUserExpr();
		$userExpr = $searchUserExpr->getExpr();
		$searchBusinessExpr = new SearchBusinessExpr();
		$busExpr = $searchBusinessExpr->getExpr();
		$searchCpcBusinessExpr = new SearchCpcBusinessExpr();
		$cpcBusExpr = $searchCpcBusinessExpr->getExpr();
		*/
		if ($cpcSearch){
			//$expr = "$catExpr * ($userExpr + $cpcBusExpr)";
			$exprBuilder = new CataExpr(new BracketExpr(new CpcBusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
			$exprBuilder->setSearchKey($searchKey);
			$expr = $exprBuilder->getExpr();
		}
		else {
			$exprBuilder = new CataExpr(new BracketExpr(new BusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
			$exprBuilder->setSearchKey($searchKey);
			$expr = $exprBuilder->getExpr();
			//$expr = "$catExpr *  ($userExpr + $busExpr)";
		}
		return $expr;	
	}

	protected function searchForResult($searchKey, $searchString) {
		$normalSearchResult = $this->getNormalSearchResult($searchKey, $searchString, self::index);
		$cpcSearchResult = $this->getCpcSearchResult($searchKey, self::cpcIndex);

		$mergeRes = $this->SearchObject->mergeCpc($normalSearchResult, $cpcSearchResult, $this->offset);
//		if (!empty($mergeRes['matches'])) {
			$this->searchResult = $mergeRes;
//		}
//		else {
//			$this->searchResult = array();
//		}
		return $this->searchResult;
	}

	protected function getNormalSearchResult($searchKey, $searchString, $index) {
		//normal search
		$expr = $this->composeExpr($searchKey);
		$this->initConditionForSearch();
		$this->SearchObject->setIndex($index);
		$this->SearchObject->setMatchMode(SPH_MATCH_EXTENDED);
		$this->SearchObject->setSortMode(SPH_SORT_EXPR, $expr);
		$this->SearchObject->search($searchString);
		$searchResult = $this->SearchObject->getSearchResult();
		return $searchResult;
	}

	protected function getCpcSearchResult($searchKey, $index) {
		//cpc search
		$cpcMax = 12000;	
		$cpcExpr = $this->composeExpr($searchKey, TRUE);
		$this->SearchObject->setIndex($index);
		$this->SearchObject->setLimit(0, $cpcMax - 1, $cpcMax);
		$this->SearchObject->setSortMode(SPH_SORT_EXPR, $cpcExpr);
		$this->SearchObject->search($searchKey);
		$cpcSearchResult = $this->SearchObject->getSearchResult();
		return $cpcSearchResult;
	}

	public function dataProcess() {
		if (!$this->setTidsAndTotalNum()) {
			return FALSE;
		}
		return TRUE;
	}

	protected function setTidsAndTotalNum() {
		$searchString = $this->getSearchString($this->wordName);
		$ret = $this->searchForResult($this->wordName, $searchString);
		$tInfos = \Snake\Libs\Base\Utilities::DataToArray($ret['matches'], 'attrs');
		$this->tids = \Snake\Libs\Base\Utilities::DataToArray($tInfos, 'twitter_id');
		//$this->totalNum = $ret['total'] < $ret['total_found'] ? $ret['total'] : $ret['total_found'];
		//$this->totalNum = $ret['total'] < $ret['total_found'] ? $ret['total'] : $ret['total_found'];
		$this->totalNum = $ret['total'];
		$this->showNum = $ret['total_found'];
		return TRUE;
	}

	public function getTids() {
		return $this->tids;	
	}

	public function getTotalNum() {
		if (empty($this->totalNum)) {
			$this->totalNum = 0;
		}
		return $this->totalNum;
	}

	public function getShowNum() {
		return $this->showNum;
	}
}

