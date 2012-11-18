<?php
/**
 * Attribute_title.class.php
 * 
 * 实现Attribute_title接口的文件
 *
 * @author ZhengXuan < xuanzheng@meilishuo.com >
 * @version 1.0
 * @package lalala //这到底是神马东西，该写神马???
 *
 */

namespace Snake\Modules\Goods;

Use Snake\Package\Goods\AttrWords;

/**
 * Attribute_title
 *
 * 传过来word_id,获取word_name的接口
 * @todo 加cache
 */

class Attribute_title extends \Snake\Libs\Controller{

	private $wordId = 0;
	private $wordName = '';

	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}

		$params = array();
		if (!empty($this->wordId)) {
			$params['word_id'] = $this->wordId;
			$params['isuse'] = 1;
		}
		else if (!empty($this->wordName)) {
			$params['word_name'] = $this->wordName;
			$params['isuse'] = 1;
		}
		else {
			$this->view = array('word_name' => '', 'word_id' => 0);
			return TRUE;
		}

		$wordInfo = AttrWords::getWordInfo($params, "/*Attribute_baike-zx*/word_name,word_id");
		if (empty($wordInfo)) {
			$this->view = array('word_name' => '', 'word_id' => 0);
			return TRUE;
		}
		else {
			$this->view = $wordInfo[0];
		}
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
			$this->wordName = $wordName;	
		}
		return TRUE;
	}

	private function setWordId() {
		$wordId = intval($this->request->REQUEST['word']);
//		if (empty($wordId)) {
//			self::setError(400, 400, 'empty word');
//			return FAlSE;	
//		}
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
