<?php
namespace Snake\Modules\Home;

USE \Snake\Package\Home\HomeRepin as HomeRepin;

/**
 * repinNotice 
 * 
 * @author ChaoGuo
 */
class Home_repin extends \Snake\Libs\Controller {
	//用户编号
	private $userId = 0;
	
	//首次加载
	private $init = FALSE;

	private $lastTime = 0;
	
	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$repinNotice = array();
		$homeRepin = new HomeRepin($this->userId, $this->lastTime);
		$repinNotice = $homeRepin->getRepinNotice();
		$this->view = array(
			'repinNotice' => $repinNotice,
		);
		return TRUE;
	}

	private function _init() {
		if (!$this->setUserId()) {
            $this->setError(400, 20150, 'empty user_id!');
            return FALSE;
        }
		if (!$this->setLastTime()) {
			$this->setError(400, 20150, 'last invalid');	
			return FALSE;
		}
		return TRUE;
	}

	private function setUserId() {
        $this->userId = $this->userSession['user_id'];
        if (empty($this->userId)) {
            return FALSE;
        }
        return TRUE;
    }

	private function setLastTime() {
		if (!empty($this->request->REQUEST['init'])) {
			$this->init = TRUE;
		}
		if ($this->init === FALSE) {
			if (isset($this->request->REQUEST['last'])) {
				if (!is_numeric($this->request->REQUEST['last'])) {
					return FALSE;
				}
				$this->lastTime = (int) $this->request->REQUEST['last'];
			}
		}
		return TRUE;
	}
}
