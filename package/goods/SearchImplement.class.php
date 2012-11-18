<?php
namespace Snake\Package\Goods;

Use Snake\Package\Search\SearchObject;
Use Snake\Package\Goods\CatalogMix AS CatalogMix;

class SearchImplement {

	static public function searchForGuoShuaiScript($cataId, $minWeight, $beginTime, $endTime) {
		if (empty($cataId)) {
			return FALSE;
		}
			
		$catalogMix = new CatalogMix($cataId);
		$catRange = $catalogMix->getIdRange();
		if (empty($catRange)) {
			return FALSE;
		}
		//$this->filter['catalog_id'] = array('field' => 'catalog_id', 'from' => $this->cataId, 'to' => $catRange['down']);
		$searchHelper = new SearchGoodsEx();
		$searchHelper->setOffset(0);
		$searchHelper->setPageSize(10000);
		$searchHelper->setWeightFilter($minWeight);
		$searchHelper->setTwitterCreateTimeFilter($beginTime, $endTime);
		$searchHelper->setCatalogFilter($cataId, $catRange['down']);
		$result = $searchHelper->search();
		return $result;
	}

}
