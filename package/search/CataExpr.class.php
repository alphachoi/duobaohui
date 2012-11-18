<?php
namespace Snake\Package\Search;
Use \Snake\Package\Goods\CataWords AS CataWords;

/**
 * rankModel中类目相关性价值的定义
 * @package search
 * @author weiwang
 * @since 2012.08.13
 */
class CataExpr extends DecorateExpr{

	/**
	 * 搜索关键词
	 *
	 * @var string
	 * @access private
	 */
	private $searchKey = "";

	public function setSearchKey($searchKey) {
		$this->searchKey = $searchKey;	
	}

	public function getExpr() {
		$catalogNameTmp = explode(")|(", $this->searchKey);
		$catalogId = 0;
		if (!empty($catalogNameTmp)) {
			$catalogName = trim("((" . end($catalogNameTmp), "\x28..\x29");
			$params = array();
			$params['catalog_name'] = $catalogName;
			$catalogFinder = new CataWords();
			$catalogFinder->setFields(array('catalog_id'));
			$catalogFinder->setValue("catalog_name", $catalogName);
			$catalogInfo = $catalogFinder->getCatalogInfo();
			$catalogId = $catalogInfo[0]['catalog_id'];
		}
		$bigTypeOfCata = floor($catalogId / pow(10, 12));
		$midTypeOfCata = floor($catalogId / pow(10, 9));
		$smallTypeOfCata = floor($catalogId / pow(10, 6));

		if ($bigTypeOfCata != 0 && $midTypeOfCata != 0 && $smallTypeOfCata != 0) {
			$this->expr = "(
				IF(
					floor(catalog_id / pow(10, 6)) == {$smallTypeOfCata} , 1, 
					IF(
						floor(catalog_id / pow(10, 9)) == {$midTypeOfCata} , 0.9, 
						IF(floor(catalog_id / pow(10, 12)) == {$bigTypeOfCata} , 0.8, 0.5)
					)
					)
				)";
		}
		else {
			$this->expr = '0.6';
		}
		$this->compose();
		return $this->expr;
	}
}
