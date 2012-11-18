<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Search\SegWords;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Recommend\Recommend;
use \Snake\Package\Manufactory\Attrmix;

class Search_attr_nine extends \Snake\Libs\Controller {
	private $wordName = '';
	private $wordId = 0;
	private $userId = 0;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$this->wordName = strip_tags($this->wordName);
		if (!empty($this->wordName)) {
			$searchKey = SegWords::segword($this->wordName);
		}
		if (empty($searchKey)) {
			$searchKey = array();	
		}
		$attrId = array();
		if (!empty($searchKey)) {
			$param = array();
			$param['word_name'] = $searchKey;
			$param['is_use'] = 1;
			$attrRes = AttrWords::getWordInfo($param, "word_id");
			if (!empty($attrRes)) {
				foreach ( $attrRes as $k=>$v) {
					if (!empty($v['word_id'])) {
						$attrId[] = $v['word_id'];
					}
				}
			}
		}
		$words = array();
		$recommendHelper = new Recommend();
		$retData = $recommendHelper->getReAttrByAidArr($attrId, 10, 4, $this->userId);
		$wordIds = array();
		foreach($retData as $winfo) {
			array_push($wordIds, $winfo['word_id']);	
		}
		$attrMixs = array();
		if (!empty($wordIds)) {
			$attrMix = new Attrmix($wordIds);
			$attrMixs = $attrMix->getAttrMix();
			$attrMixs = array_values($attrMixs);
		}
		$this->view = array('goods' => array('tInfo' => $attrMixs));
		return TRUE;
	}

	private function _init() {
		if (!$this->setWordName()) {
			return FALSE;
		}
		$this->setUserId();
		return TRUE;	
	}

	private function setUserId() {
		if (!empty($this->userSession['user_id'])) {
			$this->userId = $this->userSession['user_id'];
		}
		return TRUE;
	}

	private function setWordName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (!empty($wordName)) {
			$this->wordName = $wordName;	
		}
		return TRUE;
	}

}
