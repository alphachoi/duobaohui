<?php
namespace Snake\Package\Search;

/**
 * rankModel中类目相关性价值的定义
 * @package search
 * @author weiwang
 * @since 2012.08.13
 */
class SearchCataExpr extends SearchExpr{

	/**
	 * 搜索关键词
	 *
	 * @var string
	 * @access private
	 */
	private $searchKey = 0;

	
	function __construct(){
	}

	public function getCataExpr($searchKey) {
		$catalogNameTmp = explode(")|(", $searchKey);
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
			$cataExpr = "(
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
			$cataExpr = '0.6';
		}

		return $cataExpr;
	}

	/**
	 * 获取淘客对应的rank值
	 *
	 * @return float
	 * @access public
	 */	
	public function getCommissionValue() {
		$commissionValue = 1;
		if ($this->commission != 1) {
			$commissionValue = 0.35;
		}
		return $commissionValue;
	}

	/**
	 * 获取销售量对应的rank
	 *
	 * @return float
	 * @access public 
	 */	
	public function getSaleVolumeValue() {
		$saleVolumeValue = 1;
		if ($this->saleVolume <= 144) {
			$saleVolumeValue = pow($this->saleVolume, 1/2) / 12 * 0.9;	
		}
		return $saleVolumeValue;
	}

	/**
	 * 获取店家等级对应的rank
	 *
	 * @return float
	 * @access public 
	 */	
	public function getLevelValue() {
		$levelValue = 1;
		if ($this->level <= 8) {
			$levelValue = 0.5;
		}
		return $levelValue;
	}

	
	/**
	 * 获取相应公式的值
	 *
	 * @return float
	 * @access public
	 */	
	public function getValue() {
		//$cutTime = strtotime('2012-07-26 10:00:00');
		$commissionValue = $this->getCommissionValue();
		$saleVolumeValue = $this->getSaveVolumeValue();
		$levelValue = $this->getLevelValue();
		return pow($commissionValue * $saleVolumeValue * $levelValue, 1/3);
	}

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		return "pow( IF(commission == 1, 1, 0.35) * IF( sale_volume > 144, 1, 0.1 + pow( sale_volume, 1/2) / 12 * 0.9) * IF( level > 8, 1, 0.5), 1/3) ";
	}

}
