<?php
namespace Snake\Package\Search;

/**
 * rankModel中商业价值的定义
 * @package search 
 * @author weiwang
 * @since 2012.08.13
 */
class BracketExpr extends DecorateExpr{

	/**
	 * 获取相应公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr() {
		$expr = $this->searchExpr->getExpr();
		if (!empty($expr)) {
			$this->expr = "(" . $expr . ")";
		}
		return $this->expr;
	}

}
