<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Goods\Attribute;

/**
 * 
 * 属性推荐品牌数据接口
 *
 * @author Xuan Zheng
 * @package 宝库
 * @request_url http://snake.meilishuo.com/goods/attribute_brand?word_name=连衣裙
 * @request_method GET
 * @request_param word_name : 属性名称
 */

class Attribute_brand extends \Snake\Libs\Controller{

	/**
	 * word id
	 * @var int
	 */
	private $wordId = 0;

	/**
	 * word name
	 * @var string
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
		if (empty($this->wordName)) {
			$this->view = array();
			return TRUE;
		}
		$params = array();
		$params['word_name'] = $this->wordName;
		$params['isuse'] = 1;
		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute_brand-zx*/word_id");
		if (empty($wordInfo[0]['word_id'])) {
			$this->view = array();
			return TRUE;
		}
		$this->wordId = $wordInfo[0]['word_id'];
		$attributeHelper = new Attribute();
		$recommendBrand = $attributeHelper->getRecommendBrand($this->wordId);
		$this->view = $recommendBrand;
		return TRUE;	
	}

	/**
	 * initialize func
	 * @access private
	 * @param NULL
	 * @return boolean
	 */
	private function _init() {
		if (!$this->setBrandName()) {
			return FALSE;
		}
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
