<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，增加我的标签,同时加一个label到label表中
 * @author, Chen Hailong
 **/

use \Snake\Libs\Cache\Memcache;
use \Snake\Package\Label\Label;

class New_label extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $labelId = NULL;
	private $labelName = NULL;
	private $cache = TRUE;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$this->labelId = Label::getInstance()->addCustomLabel($this->labelName, $this->userId); 
		if (!empty($this->labelId)) {
			$cacheKey = 'person:label_' . $this->userId;
			$cacheHelper = Memcache::instance();
			$cacheHelper->delete($cacheKey);
		}
		else {
			$this->setError(400, 40120, 'add custom label failed');
			return FALSE;
		}
		$this->view = array('label_id' => $this->labelId, 'label_name' => $this->labelName);
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
		if (empty($this->userId)) {
			$this->setError(400, 40101, 'please login first, then add label');
			return FALSE;
		}
		$this->labelName = !empty($this->request->REQUEST['label_name']) ? $this->request->REQUEST['label_name'] : '';
		if (empty($this->labelName)) {
			$this->setError(400, 40301, 'label name can not be empty!');
			return FALSE;
		}
		return TRUE; 
	}

}
