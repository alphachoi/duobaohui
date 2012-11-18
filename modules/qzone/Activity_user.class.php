<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;


class Set_user extends \Snake\Libs\Controller {

	private $openId = NULL;
	private $openKey = NULL;
	
	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
	}

	private function _init() {
		$this->openId = isset($this->request->REQUEST['openid']) ? $this->request->REQUEST['openid'] : 0;
		$this->openKey = isset($this->request->REQUEST['openkey']) ? $this->request->REQUEST['openkey'] : 0;
		return FALSE;
	}

}
