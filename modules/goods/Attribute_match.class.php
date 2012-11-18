<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Recommend\Recommend;
Use Snake\Package\Goods\Attribute;
Use Snake\Package\Twitter\Twitter;
Use Snake\Package\Goods\AttrWords;

/**
 * 
 * 属性推荐搭配数据接口
 * 
 * @author Xuan Zheng
 * @package 宝库
 * @request_url http://snake.meilishuo.com/goods/attribute_match?word=***&word_name=***
 * @request_method GET
 * @request_param word :属性词 default 0                    
 * @request_param word_name : 属性词name default ''
 */
class Attribute_match extends \Snake\Libs\Controller{

	private $wordInfo = array();

	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
		
		$attributeHelper = new Attribute();
		$matchInfos = $attributeHelper->getMatchByAttr($this->wordInfo);
		$this->view = $matchInfos;
		return TRUE;

	}

	private function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}	
		if (!$this->setBrandName()) {
			return FALSE;
		}
		return TRUE;
	}

	private function setBrandName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			//$this->wordName = $wordName;	
			$this->wordInfo['word_name'] = $wordName;
		}
		return TRUE;
	}

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
		if (!empty($wordId)) {
			$this->wordInfo['word_id'] = $wordId;
		}
		return TRUE;
	}
}
