<?php
namespace Snake\Package\Search;

/**
 * rankModel中cpc商业价值的定义
 * @package search 
 * @author weiwang
 * @since 2012.08.14
 */
class CpcBusTestExpr extends DecorateExpr{

	

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		$busModulus = $this->busModulus;
		$this->expr = "$busModulus * pow( IF(commission == 1, 125000, 1) * IF( sale_volume > 144, 1, 0.1 + pow( sale_volume, 1/2) / 12 * 0.9) * IF( level > 8, 1, 0.5), 1/3)";
		parent::compose();
		return $this->expr;

	}
		
}
