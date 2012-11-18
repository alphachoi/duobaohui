<?php
namespace Snake\Package\Search;

/**
 *
 * @package search 
 * @author weiwang
 * @since 2012.08.03
 */
abstract class SearchExpr{

	/**
	 * 乘数
	 *
	 * @var string
	 * @access protected 
	 */
	protected $expr = "";

	/**
	 * 用户价值占用的比例
	 *
	 * @var float 
	 * @access protected 
	 */
	protected $userModulus = 0.7;

	/**
	 * 商业价值占用的比例
	 *
	 * @var float 
	 * @access protected 
	 */
	protected $busModulus = 0.3;


	function __construct(){
	}

	/**
	 * 获取用户价值的系数 
	 *
	 * @return float 
	 * @access public
	 */	
	public function getUserModulus() {
		return $this->userModulus;
	}

	/**
	 * 获取商业价值的系数 
	 *
	 * @return float 
	 * @access public
	 */	
	public function getBusModulus() {
		return $this->busModulus;
	}

	/**
	 * 设置用户价值的系数 
	 *
	 * @return bool
	 * @access public
	 */	
	public function setUserModulus($userModulus) {
		$this->userModulus = $userModulus;
		return TRUE;
	}

	/**
	 * 设置商业价值的系数 
	 *
	 * @return bool 
	 * @access public
	 */	
	public function setBusModulus($busModulus) {
		$this->busModulus = $busModulus;
		return TRUE;
	}

	/**
	 * 获取相应公式的值
	 *
	 * @return float 
	 * @access public
	 */	
	//abstract function getValue();

	/**
	 * 获取公式
	 *
	 * @return string
	 * @access public
	 */	
	abstract function getExpr();
	
}
