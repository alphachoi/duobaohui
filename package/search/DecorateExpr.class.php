<?php
namespace Snake\Package\Search;

/**
 *
 * @package search 
 * @author weiwang
 * @since 2012.08.13
 */

abstract class DecorateExpr extends SearchExpr{

	/**
	 * 装饰器类
	 *
	 * @var SearchExpr
	 * @access protected 
	 */
	protected $searchExpr;

	/**
	 * 不同公式间的连接符
	 *
	 * @var string
	 * @access public
	 */
	public $operator;

	function __construct(SearchExpr $expr, $operator = ""){
		$this->searchExpr = $expr;
		$this->operator = $operator;
	}

	/**
	 * 组合公式
	 *
	 * @return bool
	 * @access protected
	 */	
	protected function compose() {
		if (!empty($this->operator)) {
			$this->expr = $this->expr . $this->operator . $this->searchExpr->getExpr();	
		}
	}
	
}
