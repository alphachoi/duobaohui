<?php
namespace Snake\Package\Goods;

Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Goods\CatalogMix;
Use Snake\Package\Goods\Attribute;
Use Snake\Libs\Cache\Memcache;

Use Snake\Package\Search\Search;
Use Snake\Package\Search\SearchObject;

//Use Snake\Package\Search\SearchUserExpr;
//Use Snake\Package\Search\SearchCpcBusinessExpr;

Use Snake\Package\Search\CataExpr;
Use Snake\Package\Search\BracketExpr;
Use Snake\Package\Search\BusExpr;
Use Snake\Package\Search\UserExpr;
Use Snake\Package\Search\MainExpr;
Use Snake\Package\Search\CpcBusExpr;
Use Snake\Package\Search\CpcUserTestExpr;
Use Snake\Package\Search\CpcBusTestExpr;


class Catalog {

	static $catalogTab = array(      
		2000000000000, 2001000000000,
		2004000000000, 2006000000000,
		2009000000000, 6000000000000,
		5000000000000, 7000000000000,
		8000000000000, 9000000000000
	);

	private $keyWords = array();
	private $searchObjectForNormal = NULL;
	private $searchObjectForCpc = NULL;
	private $requestObject = NULL;
	private $indexForId = "goods_id_distributed";
	private $indexForWeight  = "goods_id_dist";
	private $indexForCpc = "goods_id_business";
	private $indexMappedByOrderBy = array('id' => 'goods_id_distributed', 'weight' => 'goods_id_dist', 'cpc' => 'goods_id_business');
	private $twitterIds = array();
	private $topTwitterIds = array();
	private $totalNum = 0;
	const daysFilter = 200;
	private $filter = array();

	private $normalSearchCacheSwitch = TRUE;
	private $cpcSearchCacheSwitch= TRUE;


	/**
	 *
	 *初始变量
	 *
	 **/
	public function __construct() {
		$this->setSearchObj();
		$this->setRequestObj();
		$this->setKeyWords($this->requestObject->getWordId());	
		$this->initializeSearchCondition($this->searchObjectForNormal);
		$this->initializeSearchCondition($this->searchObjectForCpc);
		$this->searchObjectForNormal->setUseCache($this->normalSearchCacheSwitch);
		$this->searchObjectForCpc->setUseCache($this->cpcSearchCacheSwitch);
	}	

	private function useAttrSearch() {
		$cataKeywords = $this->getCatalogWords($this->requestObject->getCataId());
		if (!empty($cataKeywords)) {
			foreach ($cataKeywords as $v) {
				if ($v['word_id'] == $this->requestObject->getWordId() && strpos($v['group_name'], "当季热款") !== FALSE) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
		
	private function setSearchObj() {
		$this->searchObjectForNormal = new SearchObject('normal');
		$this->searchObjectForCpc = new SearchObject('cpc');
		return TRUE;
	}

	private function setRequestObj() {
		$registry = Registry::instance();
		$this->requestObject = $registry->getRequest();
		return !empty($this->requestObject);
	}

	public function search() {
		$orderby = $this->requestObject->getOrderBy();
		$price = $this->requestObject->getPrice();
		if ($orderby == 'weight' && empty($price)) {
			$offset = $this->requestObject->getOffset();
			$pageSize = $this->requestObject->getPageSize();
			$this->getTopTwitters($offset, $pageSize);
		}
		//var_dump( $this->requestObject->getOffset(), $this->requestObject->getPageSize());
		if(!$this->useAttrSearch()) {
			$this->catalogSearch();
		}
		else {
			$this->attrSearch();
		}
	}

	private function catalogSearch() {
		$this->searchObjectForNormal->setFilter('verify_stat', array(1,2));
		//过滤掉被删除的宝贝
		$this->searchObjectForNormal->setFilter('goods_id_attr', array(0), TRUE);
		$this->searchInCatalog();
		if ($this->totalNum < 1200 ){// && $this->totalNum > 0) {
			$this->searchObjectForNormal->resetFilters();
			$this->searchObjectForNormal->setFilter('verify_stat', array(0,1,2));
			$this->searchInCatalog();	
		}
		return TRUE;	
	}

	private function attrSearch() {
		$this->filter['verify_stat'] = array('field' => 'verify_stat', 'from' => 1, 'to' => 2 );
		$this->priceFilter($this->requestObject->getPrice());
		$this->searchInAttr();	
		if ($this->totalNum < 1200 ){//&& $this->totalNum > 0) {
			unset($this->filter['verify_stat']);
			$this->searchInAttr();	
		}
		return TRUE;
	}

	private function searchInCatalog() {
		$searchKey = $this->getSearchKey($this->keyWords);
		$normalSearchResult = $this->normalSearch($searchKey);
		$cpcSearchResult = $this->cpcSearch($searchKey);
		$pageSize = $this->requestObject->getPageSize();
		if (empty($pageSize)) {
			$normalSearchResult['matches'] = array();
			$cpcSearchResult['matches'] = array();
//			$normalSearchResult['total_found'] += $cpcSearchResult['total_found'] + count($this->topTwitterIds);
//			$normalSearchResult['total'] += $cpcSearchResult['total'] + count($this->topTwitterIds);
		}

		$searchResult = $this->searchObjectForNormal->mergeCpc($normalSearchResult, $cpcSearchResult, $this->requestObject->getOffset());
		$this->searchResultConvert($searchResult);
		return TRUE;
	}

	private function searchInAttr() {
		$attrHelper = new AttributeBase();
		$attrResult = $attrHelper->getGoodsByAttr( $this->keyWords, $this->requestObject->getOffset(), $this->requestObject->getPageSize(), $this->requestObject->getOrderBy(), array(), $this->filter, '');
		$searchResult = $this->searchResultConvert($attrResult);
		return TRUE;
	}

	private function initializeSearchCondition($searchObject) {
		if (empty($searchObject)) {
			return FALSE;
		}
		$endTime = $this->timeByTenMin();
		$beginTime = $endTime - self::daysFilter * 86400;
		$searchObject->setFilterRange('goods_author_ctime', $beginTime, $endTime);
		return TRUE;
	}

	private function normalSearch($searchKey) {
		$this->setTimeFilter($this->searchObjectForNormal, $this->requestObject->getOrderBy());
		$this->setPriceFilter($this->searchObjectForNormal, $this->requestObject->getPrice());
		$this->setCataIdFilter($this->searchObjectForNormal, $this->requestObject->getCataId());
		$this->setMatchModeByWordsNum($this->searchObjectForNormal, $this->keyWords);
		$this->setSortModeByOrderBy($this->searchObjectForNormal, $this->requestObject->getOrderBy());
		$this->setLimit($this->searchObjectForNormal, $this->requestObject->getOffset(), $this->requestObject->getPageSize());
		$this->setIndexByOrderBy($this->searchObjectForNormal, $this->requestObject->getOrderBy());

		$this->searchObjectForNormal->search($searchKey);
		$searchResult = $this->searchObjectForNormal->getSearchResult();
		return $searchResult;
	}

	private function cpcSearch($searchKey) {
		if ($this->requestObject->getOrderBy() == 'id') {
			return FALSE;
		}
//		$this->setLimit($this->searchObjectForCpc, 0, 12000);
		$this->setTimeFilter($this->searchObjectForCpc, $this->requestObject->getOrderBy());
		$this->setPriceFilter($this->searchObjectForCpc, $this->requestObject->getPrice());
		$this->setMatchModeByWordsNum($this->searchObjectForCpc, $this->keyWords);
		$this->setCataIdFilter($this->searchObjectForCpc, $this->requestObject->getCataId());
		$this->setCpcSortMode($this->searchObjectForCpc);
		$this->setIndexByOrderBy($this->searchObjectForCpc, 'cpc');

		$this->searchObjectForCpc->setFilter('verify_stat', array(1,2));

		$this->searchObjectForCpc->search($searchKey);
		$searchResult = $this->searchObjectForCpc->getSearchResult();
		return $searchResult;
	}

	private function setIndexByOrderBy($searchObject, $orderBy) {
		if (empty($searchObject)) {
			return FALSE;
		}
		$searchObject->setIndex($this->indexMappedByOrderBy[$orderBy]);
		return TRUE;
	}

	private function setLimit($searchObject, $offset, $pageSize) {
		if (empty($searchObject)) {
			return FALSE;	
		}
		$searchObject->setLimit($offset * $pageSize, $pageSize);
		return FALSE;
	}


	/**
	 *由初始变量设置搜索条件 && catalog keyword的相关
	 *
	 */
	private function setPriceFilter ($searchObject, $price) {
		if (!is_array($price) || empty($searchObject)) {
			return FALSE;
		}
		$searchObject->SetFilterFloatRange('goods_price', $price['from'], $price['to']);
		return TRUE; 
	}

	private function setVerifyFilter () {
		$this->filter['verify_stat'] = array('field' => 'verify_stat', 'from' => 1, 'to' => 2 );
		return TRUE;
	}


	private function setTimeFilter ($searchObject, $orderby = 'weight') {
		if ($orderby != 'weight' || empty($searchObject)) {
			return FALSE;	
		}
		$time = $this->timeByTenMin();
		$searchObject->setFilterRange('goods_author_ctime', $time - 86400 * 60, $time);
		return TRUE;
	}


	private function setCataIdFilter($searchObject, $cataId = 0) {
		if (empty($cataId) || empty($searchObject)) {
			return FALSE;
		}
		$catalogMix = new CatalogMix($cataId);
		$catRange = $catalogMix->getIdRange();
		if (empty($catRange)) {
			return FALSE;
		}
		$searchObject->SetFilterRange('catalog_id', $cataId, $catRange['down']);
		return TRUE;
	}

	private function priceFilter ($price = 'all') {
		if ($price != "all") {
			if (isset($price['from']) && isset($price['to'])) {
				$this->filter['goods_price'] = array( 'field'  => 'goods_price', 'from' => $price['from'], 'to' => $price['to']);
			}
		}
		return TRUE; 
	}   

	private function setSortModeByOrderBy($searchObject, $orderBy = 'weight') {
		if (empty($searchObject)) {
			return FALSE;	
		}	
		if ($orderBy == 'id') {
			$searchObject->setSortMode(SPH_SORT_EXTENDED, 'goods_id_attr DESC');
		}
		else {
			$searchObject->setSortMode(SPH_SORT_EXPR, $this->getNormalSearchExpr($searchObject));
		}
		return TRUE;
	}
	
	private function setCpcSortMode ($searchObject) {
		if (empty($searchObject)) {
			return FALSE;	
		}	
		$searchObject->setSortMode(SPH_SORT_EXPR, $this->getCpcSearchExpr($searchObject));
		return TRUE;	
	}

	private function getCatalogWords($cataId = 0) {
		if (empty($cataId)) {
			return FALSE;	
		}
		$catalogKeywords = AttrWords::getCatalogKeyWords($cataId);
		return $catalogKeywords;
	}


	private function getWordInfo($wordId) {
		if (empty($wordId)) {
			return FALSE;
		}
		$param = array();
		$param['word_id'] = $wordId;
		$wordInfo = AttrWords::getWordInfo($param, "/*Catalog-zx*/ word_id,word_name");
		if (empty($wordInfo)) {
			return FALSE;
		}
		$sameInfo   = AttrWords::getSameWord($wordId);
		$keywords = array();
		$keywords[] = $wordInfo[0]['word_name'];

		if (!empty($sameInfo)) {
			foreach ($sameInfo as $value) {
				$keywords[] = $value['word_name'];
			}
		}
		return $keywords;
	}


	public function getTids() {
		$twitterIds = array_merge($this->topTwitterIds, $this->twitterIds);
		return $twitterIds;	

	}

	public function getTotalNum() {
		return $this->totalNum;
	}

	public function setKeyWords($wordId) {
		$this->keyWords = $this->getWordInfo($wordId);
		return TRUE;
	}

	private function getSearchKey($keyWords = array()) {
		if (empty($keyWords)) {
			$keyWords = "";
		}
		else {
			if( count($keyWords) > 30 ) {
				//词数太大时需要用SPH_MATCH_EXTENDED模式
				$keyWords = implode('|', $keyWords);
			} 
			else {
				$keyWords = implode(' ', $keyWords);
			}
		}
		return $keyWords;
	}

	private function setMatchModeByWordsNum($searchObject, $keyWords = array()) {
		if (empty($searchObject)) {
			return FALSE;
		}
		if (empty($keyWords)) {
				$searchObject->SetMatchMode(SPH_MATCH_EXTENDED);
		}
		else {
			if( count($keyWords) > 30 ) {
				//词数太大时需要用SPH_MATCH_EXTENDED模式
				$searchObject->SetMatchMode( SPH_MATCH_EXTENDED );
			} 
			else {
				$searchObject->SetMatchMode( SPH_MATCH_ANY );
			}
		}
		return TRUE;
	}

	private function getNormalSearchExpr($searchObject) {
		$exprBuilder = new CataExpr(new BracketExpr(new BusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
		$exprBuilder->setSearchKey($keywords);
		$searchExpr = $exprBuilder->getExpr();
		return $searchExpr;

	}
	
	private function getCpcSearchExpr($searchObject) {
		$exprBuilder = new CataExpr(new BracketExpr(new CpcBusExpr(new CpcUserTestExpr(new MainExpr(), ""), "+"), ""), "*");
		$exprBuilder->setSearchKey($keywords);
		$searchExpr = $exprBuilder->getExpr();
		return $searchExpr;
	}

	public function searchResultConvert($searchResult = array()) {
		$this->totalNum = min($searchResult['total'], $searchResult['total_found']);
		if (empty($searchResult['matches'])) {
			return FALSE;	
		}
		$this->twitterIds = array();
		$topTwitters = new TopTwitter();
		$topTwittersInCata = $topTwitters->getCatalogTopTwitterIds($this->requestObject->getCataId(), $this->requestObject);
		foreach ($searchResult['matches'] as $attrs) {
			if (is_array($topTwittersInCata) && in_array($attrs['attrs']['twitter_id'], $topTwittersInCata)) {
				//其实这块减了并没有太多用处
				$this->totalNum -= 1;
				continue;	
			}
			array_push($this->twitterIds, $attrs['attrs']['twitter_id']);			
		}
		return TRUE;
	}

	private function timeByTenMin() {
		$todayTime = strtotime(date("Y-m-d"));
		$timeDetail = localtime(time(), TRUE);
		$whichTen = ($timeDetail['tm_hour'] * 3600 + $timeDetail['tm_min'] * 60 + $timeDetail['tm_sec']) / 600;
		$whichTen = floor($whichTen);
		return $todayTime + $whichTen * 600;
	}

	private function getTopTwitters($offset = 0, $pageSize = 0) {

		$topTwitters = new TopTwitter();
		$topTwitterIds = $topTwitters->getCatalogTopTwitterIds($this->requestObject->getCataId(), $this->requestObject);
		if (!is_array($topTwitterIds)) {
			return FALSE;	
		}
		$topTwitterIds = array_slice($topTwitterIds, $offset * $pageSize, $pageSize);
		if (!empty($topTwitterIds)) {
			$pageSize = ($pageSize - count($topTwitterIds)) >= 0 ? $pageSize - count($topTwitterIds) : 0;
			$this->requestObject->setFrame(0);
			$this->requestObject->setPage(0);
			$this->requestObject->setPageSize($pageSize);
		}
		$this->topTwitterIds = $topTwitterIds;
		return TRUE;
	}

	static public function isCatalogTab($cataId) {
		$isCatalogId = in_array($cataId, self::$catalogTab);
		if (!$isCatalogId) {
			return FALSE;
		}
		return TRUE;
	}
}
