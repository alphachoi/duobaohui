<?php
namespace Snake\Package\Search;

/**
 *
 * @package search 
 * @author weiwang
 * @since 2012.08.13
 */
class MainExpr extends SearchExpr{

	/**
	 * 获取公式
	 *
	 * @return string
	 * @access public
	 */	
	public function getExpr(){
		return $this->expr = "";		
	}
	
}
