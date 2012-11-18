<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;


/**
 * 
 * 属性百科数据接口
 * 
 * 前端调用获取属性页页面的百科数据接口
 *
 * @author Xuan Zheng
 * @package 宝库
 * @request_url http://snake.meilishuo.com/goods/attribute_baike?word=***&word_name=***
 * @request_method GET
 * @request_param word :属性词 default 0                    
 * @request_param word_name : 属性词name default ''
 */
class Attribute_baike extends \Snake\Libs\Controller{

	/**
	 * attr word id
	 * @var 0 
	 */
	private $wordId = 0;

	/**
	 * attr word name 在没有word id时,用word name也可以请求
	 * @var 0 
	 */
	private $wordName = '';

	/**
	 * to run
	 * @access public
	 * @param NULL
	 * @return boolean
	 */
	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}

		$params = array();
		if (!empty($this->wordId)) {
			$params['word_id'] = $this->wordId;
			$params['isuse'] = 1;
		}
		elseif (!empty($this->wordName)) {
			$params['word_name'] = $this->wordName;
			$params['isuse'] = 1;
		}
		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute_baike-zx*/word_name,msg");
		if (empty($wordInfo)) {
			$this->view = array();
		}
		else {
			$this->view = $wordInfo[0];
		}
		return TRUE;	
	}

	/**
	 * initialize func
	 * @access private
	 * @param NULL
	 * @return boolean
	 */
	private function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}	
		if (!$this->setBrandName()) {
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * use request to set word id
	 * @access private
	 * @param NULL
	 * @return boolean
	 */
	private function setWordId() {
		$wordId = intval($this->request->REQUEST['word']);
		if (!empty($wordId) && !is_numeric($wordId)) {
			self::setError(400, 400, 'word is not number');
			return FALSE;
		}
		if ($wordId < 0) {
			self::setError(400, 400, 'bad word');
			return FALSE;
		}
		$this->wordId = $wordId;
		return TRUE;
	}

	/**
	 * use request to set word name
	 * @access private
	 * @param NULL
	 * @return boolean
	 */
	private function setBrandName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			$this->wordName = $wordName;	
		}
		return TRUE;
	}
}
