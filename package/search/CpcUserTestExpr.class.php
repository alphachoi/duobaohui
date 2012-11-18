<?php
namespace Snake\Package\Search;

/**
 * rankModel中cpc用户价值的定义for test
 * @package search 
 * @author weiwang
 * @since 2012.08.16
 */
class CpcUserTestExpr extends DecorateExpr{
	

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		$userBuilder = new UserExpr(new MainExpr(), "");	
		$rankLikeExpr = $userBuilder->getRankLikeExpr();
		$timeExpr = $userBuilder->getTimeExpr();
		$userModulus = $this->userModulus;
		$searchTime = time();
		$this->expr = "$userModulus * pow( 
			( IF( verify_stat == 0, 0.2, 0) + IF( verify_stat == 1, 0.4, 0 ) + IF( verify_stat == 2, 1, 0) + IF( verify_stat == 5, 0.2, 0) ) *
			$rankLikeExpr *
			$timeExpr, 1/3
		) 
		";	
		parent::compose();
		return $this->expr;
	}
		
}
