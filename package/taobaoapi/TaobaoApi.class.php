<?php
namespace Snake\Package\TaobaoApi;

abstract class TaobaoApi{
	
	/**
	 * 要获取的淘宝内容
	 *
	 * @var string 
	 * @access protected
	 */
	protected $fields = "";

	/**
	 * 要调用的淘宝api方法名
	 *
	 * @var string 
	 * @access protected
	 */
	protected $method = NULL;

	/**
	 * 淘宝api的申请人在淘宝的昵称
	 *
	 * @var string 
	 * @access protected
	 */
	protected $nickname = TAOBAO_NICKNAME;

	/**
	 *
	 * @return TaobaoApi
	 */
    public function __construct() {
	}

	/**
	 * 设置要获取的内容
	 *
	 * @param string $fields 參數1
	 * @return void
	 * @access public
	 */	
	public function setFields($fields) {
		if (empty($fields)) {
			throw new \Exception('taobaoApi fields empty!');
		}
		$this->fields = $fields;		
	}

	/**
	 * 设置要获取的内容
	 *
	 * @return string
	 * @access public
	 */	
	public function getFields() {
		return $this->fields;
	}

	/**
	 * 获取淘宝api的方法名称
	 *
	 * @return string
	 * @access public
	 */	
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

