<?php
namespace Snake\Modules\User;

USE \Snake\Package\Search\SegWords AS SegWords;
USE \Snake\Package\Goods\AttrWords AS AttrWords;
USE \Snake\Package\Recommend\Recommend AS Recommend;
USE \Snake\Package\Manufactory\Attrmix AS Attrmix;
USE \Snake\Package\User\UserCommonList AS UserCommonList;

/**
 * 搜索推荐
 *
 */
class Search_recommend extends \Snake\Libs\Controller {
	private $wordName = '';
	private $userId = 0;
	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
		$searchKey = SegWords::segword(strip_tags($this->wordName));
        if (empty($searchKey)) {
            $seachKey = array();    
        }   
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

        $words = array();
        $recommendHelper = new Recommend();

		$retData = $recommendHelper->getReUserByAidArr($attrId, 10, 4, $this->userId);
		if (empty($retData)) {
			$this->view = array();
			return FALSE;
		}
        $uIds = array();
		foreach ($retData as $k=>$v) {
			$uIds[] = $v['user_id'];
		}
		
		$userCommon = new UserCommonList();
		$userCommonList = $userCommon->getSearchCommonInfo($uIds, $this->userId);	
		$this->view = array(
			'user' => array(
				'uInfo' => array_values($userCommonList),
			),
		);
		return TRUE;
	}	

	private function _init() {
		if (empty($this->request->REQUEST['word_name'])) {
			$this->setError(400, 40150, 'empty word_name');
			return FALSE;
		}	
		$this->wordName = $this->request->REQUEST['word_name'];
		if (!empty($this->userSession['user_id'])) {
			$this->userId = $this->userSession['user_id'];
		}
		return TRUE;
	}
}
