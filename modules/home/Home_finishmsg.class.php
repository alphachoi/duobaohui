<?php
namespace Snake\Modules\Home;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;

class Home_finishmsg extends \Snake\Libs\Controller {
	
	private $lastTid = 0;
	private $userId = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$cacheKey = 'HOME_LAST_TID_' . $this->userId;
	    $cacheHelper = Memcache::instance();
		$cacheHelper->set($cacheKey, $this->lastTid, 3600);
		return TRUE;
	}

	public function _init() {
		if (!$this->setUserId()) {
			$this->setError(400, 200150, 'empty user_id');
			return FALSE;
		}
		if (!$this->setLastTid()) {
			$this->setError(400, 200150, 'last_tid invalid');
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

	private function setLastTid() {
		$lastTid = isset($this->request->REQUEST['last_tid']) ? $this->request->REQUEST['last_tid'] : UserHomePosterTimeline::getLastTid($this->userId);	
		if (!is_numeric($lastTid) || $lastTid < 0) {
            return FALSE;
        }   
        $this->lastTid = (int)$lastTid;
        return TRUE;
	}
}
