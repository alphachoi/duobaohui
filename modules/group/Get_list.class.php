<?php
/**
 Groupheader.class.php
 */

namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache;
use \Snake\Package\Group\GroupList;

class Get_list extends \Snake\Libs\Controller {

	private $groupId = 0;
	private $userId = 0;
	private $elite = array();
	private $offset = 0;
	private $limit = 20;

	public function run() {
		if (!$this->_init()) {
			$this->view = array();
		}
		$GroupListHelper = new GroupList($this->groupId);
		$this->view = $GroupListHelper->getGroupList($this->userId, $this->elite, $this->offset, $this->limit);
		return TRUE;
	}

	private function _init() {
		$this->groupId = $this->request->GET['group_id'];
		if (empty($this->groupId)) {
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		$isElite = $this->request->GET['elite'];
		if (empty($isElite)) {
			$this->elite = array(0,1,2);
		}
		else {
			$this->elite = array(1,2);
		}
		$this->offset = isset($this->request->GET['page']) ? $this->request->GET['page'] * $this->limit : 0;
		return TRUE; 
	}

}


