<?php
namespace Snake\Modules\Goods;
/**
 * @author xuanzheng@meilishuo.com 
 */

//Use Snake\Package\Cms\CmsIndexWelcome;
Use Snake\Package\Group\Groups;
/**
 * Guang热榜的推荐杂志社
 * @todo huazhulin的接口替换
 */

class Popular_group extends \Snake\Libs\Controller {
	private $userId = 0;
	private $magFavor = NULL;
	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
		$groupHelper = new Groups();
		$result = $groupHelper->getPopularGroupForGuang($this->userId, $this->magFavor);
		if (empty($result)) {
			$this->view = array();
			return TRUE;
		}
		$this->view = $result;
		return TRUE;
		/*$groupHelper = new Groups();
		$groups = $groupHelper->getGroupSquareInfo($result, $this->userId);	
		if (!empty($groups) && is_array($groups)) {
			$groups = array_values($groups);
		}
		else {
			$groups = array();
		}
		$this->view = $groups;
		return TRUE;*/
	}

	private function _init() {
		$this->setUserId();
		$this->magFavor = isset($this->request->REQUEST['magfavor']) ? $this->request->REQUEST['magfavor'] : 0;
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}
}
