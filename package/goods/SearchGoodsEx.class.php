<?php
namespace Snake\Package\Goods;

class SearchGoodsEx extends SearchGoods {

	function __construct() {
		parent::__construct();
	}
	
	public function setWeightFilter($min = 0, $max = 50000) {
		//$min = $min + 10000;
		//$max = $max + 10000;
		$this->SearchObject->setFilterRange('rank_like', $min, $max);
		return TRUE;
	}

	public function setTwitterCreateTimeFilter($begin, $end) {
		$this->SearchObject->setFilterRange('twitter_create_time', $begin, $end);
		return TRUE;
	}

	public function setCatalogFilter($beginCataId, $endCataId) {
		$this->SearchObject->setFilterRange('catalog_id', $beginCataId, $endCataId);
		return TRUE;
	}

	public function search() {
		$searchString = $this->getSearchString($this->wordName);
		$ret = $this->searchForResult($this->wordName, $searchString);
		return $ret;
	}
}

