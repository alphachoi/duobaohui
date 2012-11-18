<?php
namespace Snake\Modules\Group;

Use Snake\Package\Search\SegWords;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Recommend\Recommend;
use \Snake\Package\Manufactory\Attrmix;
use \Snake\Package\Group\Groups;

class Search_recommend extends \Snake\Libs\Controller {
    private $wordName = ''; 
    private $wordId = 0;
    private $userId = 0;

    public function run() {
        if (!$this->_init()) {
            return FALSE;
        }
        $searchKey = SegWords::segword(strip_tags($this->wordName));
        if (empty($searchKey)) {
            $seachKey = array();    
			$attrId = array();
        }
		else {
			if (!empty($searchKey)) {
				$param = array();
				$param['word_name'] = $searchKey;
				$param['is_use'] = 1;
				$attrRes = AttrWords::getWordInfo($param, "word_id");
				$attrId = array();
				if (!empty($attrRes)) {
					foreach ($attrRes as $k=>$v) {
						if (!empty($v['word_id'])) {
							$attrId[] = $v['word_id'];
						}   
					}
				}
			} 
		}
        $words = array();
        $recommendHelper = new Recommend();
        $retData = $recommendHelper->getReGroupByAidArr($attrId, 10, 8, $this->userId);
		if (empty($retData)) {
			$this->view = array();
			return FALSE;
		}
		foreach ($retData AS $key => $value) {
			$groupIds[] = $retData[$key]['group_id'];
		}
		$groupHelper = new Groups();
		$groupInfo = array();
		$groupInfo = $groupHelper->getGroupSquareInfo($groupIds, $this->userId);
		$groupInfo = array_values($groupInfo);
		$groupInfo = array_slice($groupInfo, 0, 4);
		$result = array();
		$result['magazine']['gInfo'] = $groupInfo;
		$this->view = $result;
		return TRUE;
	}

	private function _init() {
		$this->wordName = isset($this->request->REQUEST['word_name']) ? $this->request->REQUEST['word_name'] : "";
		if (empty($this->wordName)) {
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}


}

