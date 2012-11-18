<?php
namespace Snake\Modules\Medal;

/**
 * 登录发勋章活动
 * @author yishuliu@meilishuo.com
 **/

Use \Snake\Package\Medal\Medal;			
Use \Snake\Package\Medal\MedalLib;			
Use \Snake\Libs\Cache\Memcache;

class Medal_activity extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $medalId = NULL;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}

		$userMedal = $this->_getMedalInfo();
		if (empty($userMedal)) {
			$this->view = array();
			return;
		}
		$this->view = array('status' => 1);
	}
	
	private function _init() {
		$this->setUserId();
        if (empty($this->userId)) {
			$this->setError(400, 40101, 'userId is empty');
            return FALSE;
        }   
		$this->setMedalId();
        if (empty($this->medalId)) {
			$this->setError(400, 40302, 'medal is illeage');
            return FALSE;
        }   
        return TRUE;
	}

    private function setUserId() {
        $this->userId = $this->userSession['user_id']; //7580696;
    }  
	
    private function setMedalId() {
        $this->medalId = isset($this->request->REQUEST['medal']) ? $this->request->REQUEST['medal'] : 72;
        if (!is_numeric($this->medalId) || $this->medalId < 0) {
            $this->setError(400, 40302, 'medal is illeage');
            return FALSE;
        }   
    }  

	private function _getMedalInfo() {
		//$medalId = 72; 
		$medalLibHelper = new MedalLib();
		$medalLibHelper->medalLib($this->userId);
		$medalLibHelper->addMedalForActivity($this->medalId);
		return TRUE;
	}
}
