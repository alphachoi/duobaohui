<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，增加我的标签
 * @author, Chen Hailong
 **/

use \Snake\Libs\Cache\Memcache;
use \Snake\Package\Label\Label;

class Remove_label extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $labelId = NULL;
	private $cache = TRUE;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$status = Label::getInstance()->deleteLabel($this->userId, $this->labelId);
		if (!empty($status)) {
			$cacheKey = 'person:label_' . $this->userId;
			$cacheHelper = Memcache::instance();
			$cacheHelper->delete($cacheKey);
		}
		$this->view = array('status' => $status);
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
		if (empty($this->userId)) {
			$this->setError(400, 40101, 'please login first, then add label');
			return FALSE;
		}
		$this->labelId = !empty($this->request->REQUEST['label_id']) ? $this->request->REQUEST['label_id'] : 0;
		if (empty($this->labelId)) {
			$this->setError(400, 40101, 'please login first, then add label');
			return FALSE;
		}
		return TRUE; 
	}

}
