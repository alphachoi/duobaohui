<?php
namespace Snake\Package\Search;

/**
 * rankModel中cpc商业价值的定义
 * @package search 
 * @author weiwang
 * @since 2012.08.13
 */
class CpcBusExpr extends DecorateExpr{

	

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		//$searchBusinessExpr = new SearchBusinessExpr();
		//$busExpr = $searchBusinessExpr->getExpr();
		$busBuilder = new BusExpr(new MainExpr(), "");
		$busExpr = $busBuilder->getExpr();
		$busModulus = $this->busModulus;
		$now = time();
		$this->expr = "$busModulus * IF( initial_commercial_value + later_commercial_rate > 0 , 
			IF (($now - start_time) <= 3*3600*24,    
			initial_commercial_value, 
			IF ( later_commercial_rate *  pow( IF(commission == 1, 1, 0.35) * IF( sale_volume > 144, 1, 0.1 + pow( sale_volume, 1/2) / 12 * 0.9) * IF( level > 8, 1, 0.5), 1/3) > 1.0,
			1.0,
			later_commercial_rate *  pow( IF(commission == 1, 1, 0.35) * IF( sale_volume > 144, 1, 0.1 + pow( sale_volume, 1/2) / 12 * 0.9) * IF( level > 8, 1, 0.5), 1/3) 
			)
		), 
		$busExpr    
		)";
		parent::compose();
		return $this->expr;
	}
		
}
