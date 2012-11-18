<?php
namespace Snake\Modules\Goods;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Goods\Attribute;
Use Snake\Package\Goods\Category;

class Attribute_keywords_recommend extends \Snake\Libs\Controller{

	private $wordId = 0;
	private $wordName = '';
	private $num = 20;

	public function run() {
		$categoryTopId = $this->request->REQUEST['cata_top_id'];
		if (empty($categoryTopId)) {
			self::setError(400, 400, 'empty word_id&&word_name');
			return FALSE;
		}

		$categoryM = new Category();
		$response = $categoryM->getCategory($categoryTopId);
		$this->view = $response;

		return true;
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

		if (!$this->_init()) {
			return FALSE;	
		}

		if (empty($this->wordId) && empty($this->wordName)) {
			self::setError(400, 400, 'empty word_id&&word_name');
			return FALSE;
		}

		if (!empty($this->wordName) && empty($this->wordId)) {
			$params = array();
			$params['word_name'] = $this->wordName;
			$params['isuse'] = 1;
			$wordInfo = AttrWords::getWordInfo($params, "/*Attribute_keywords_recommend-zx*/word_id,word_name");
			if (!empty($wordInfo[0]['word_id'])) {
				$this->wordId = $wordInfo[0]['word_id'];
			}
		}
		if (empty($this->wordId)) {
			$this->view = array();
			return TRUE;
		}

		$attributeObj = new Attribute();
		$response = $attributeObj->getRecommendKeywords($this->wordId, $this->num);
		$this->view = $response;
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

	private function setBrandName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			$this->wordName = $wordName;	
		}
		return TRUE;
	}
}
