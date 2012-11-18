<?php

namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Goods\Attribute;

/**
 * 
 * 属性最佳款式
 * 
 * 前端调用获取属性页的最佳搭配的数据接口
 *
 * @author Xuan Zheng
 * @package 宝库
 * @request_url http://snake.meilishuo.com/goods/attribute_best_style?word=34295
 * @request_method GET
 * @request_param word :属性词 default 0                    
 */

class Attribute_best_style extends \Snake\Libs\Controller{

	/**
	 * word id
	 * @var int
	 */
	private $wordId = 0;

	/**
	 * 返回最佳款式的数量
	 * @const 4 
	 */
	const returnNum = 4;

	/**
	 * to run
	 * @access public
	 * @param NULL
	 * @return boolean
	 */
	function run () {
		if (!$this->_init()) {
			return FALSE;	
		}
				
		
		$attributeHelper = new Attribute();
		$bestStyles = $attributeHelper->getBestStyleByAttr($this->wordId, self::returnNum);

		$this->view = $bestStyles;
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
		if (empty($wordId)) {
			self::setError(400,400, 'empty word');
			return FALSE;
		}
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
}
