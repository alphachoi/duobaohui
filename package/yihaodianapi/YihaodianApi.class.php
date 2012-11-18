<?php
namespace Snake\Package\TaobaoApi;

abstract class TaobaoApi{
	/**
	 * api method name
	 * @var string
	 * @access protected
	 */
	protected $method = NULL;

	/**
	 * tracker_u 网盟标示
	 * @var int
	 * @access protected
	 */
	protected $trackerU = YIHAODIAN_TRACKER_U;



	public function __construct() {
	}

	public function getMethod() {
		return $this->method;
	}

	/**
	 * 不同的api需要实现不同的策略
	 *
	 * @return array
	 * @access public
	 */	
	abstract public function getParamArr();

} 
