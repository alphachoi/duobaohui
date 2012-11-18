<?php
namespace Snake\Package\Search;

/**
 * rankModel中cpc商业价值的定义
 * @package search 
 * @author weiwang
 * @since 2012.08.14
 */
class CpcCataUserExpr extends DecorateExpr{


	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		$userExprBuilder = new UserExpr(new MainExpr(), "");
		$verifyStatExpr = $userExprBuilder->getVerifyStatExpr();
		$rankLikeExpr = $userExprBuilder->getRankLikeExpr("catalog_count_like");
		$timeExpr = $userExprBuilder->getTimeExpr();
		$userModulus = $this->userModulus;
		$this->expr = "$userModulus * pow( 
			$verifyStatExpr *
			$rankLikeExpr *
			$timeExpr, 1/3)";	
		parent::compose();
		return $this->expr;
	}
		
}
