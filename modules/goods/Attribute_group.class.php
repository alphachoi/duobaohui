<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\Attribute;

class Attribute_group extends \Snake\Libs\Controller {


//	private $wordId = 0;
//	private $wordName = '';
	private $wordInfo = array();
	private $userId = 0;
	private $magFavor = '';


	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
//		if (empty($this->wordInfo)) {
//			return FALSE;
//		}
		$attribute = new Attribute();
		$groups = $attribute->getNineGroupByAttr($this->wordInfo, $this->userId, $this->magFavor);
		$this->view = $groups;
		return TRUE;
	}

	private function _init() {
		if (!$this->setWordId()) {
			return FALSE;
		}	
		if (!$this->setBrandName()) {
			return FALSE;
		}
		if (!$this->setUserId()) {
			return FALSE;
		}
		if (!$this->setMagFavor()) {
			return FALSE;
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
		$this->wordInfo['word_id'] = $wordId;
		return TRUE;
	}

	private function setBrandName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			$this->wordInfo['word_name'] = $wordName;	
		}
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	private function setMagFavor() {
		$this->magFavor = $this->request->REQUEST['magfavor'];
		return TRUE;
	}
}
