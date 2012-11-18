<?php
namespace Snake\Modules\Medal;

/**
 * 登录发勋章活动中判断是否发送该勋章
 * @author yishuliu@meilishuo.com
 **/

Use \Snake\Package\Medal\Medal;			
Use \Snake\Package\Medal\MedalLib;			
Use \Snake\Libs\Cache\Memcache;

class Check_medal extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $medalId = NULL;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}

		$userMedal = $this->_getMedalInfo();
		if (empty($userMedal['num'])) {
			$this->view = array('hasMedal' => 0, 'icon' => $userMedal['icon']);
			return;
		}
		$this->view = array('hasMedal' => 1, 'icon' => $userMedal['icon']);
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
		$medalHelper = new Medal();
		$exist['num'] = $medalHelper->getUserMedalNum($this->userId, $this->medalId);
		$medalInfo = $medalHelper->getMedalInfoByMids(array($this->medalId));
		$exist['icon'] = !empty($medalInfo[$this->medalId]['medal_icon']) ? 'http://i.meilishuo.net/css/images/medal/icons/' . $medalInfo[$this->medalId]['medal_icon'] : '';
		return $exist;
	}
}
