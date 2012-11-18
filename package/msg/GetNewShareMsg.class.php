<?php
namespace Snake\Package\Msg;

USE \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;
USE \Snake\Package\Msg\Helper\DBMsgHelper AS DBMsgHelper;
USE \Snake\libs\Cache\Memcache AS Memcache;

class GetNewShareMsg {
	
	private $msgInfo = array();
	private $userId = NULL;
	private $cacheKey = NULL;
	private $from = 'web';

	public function __construct($user_id, $cacheKey, $from = 'web') {
		$this->userId = $user_id;
		$this->cacheKey = $cacheKey;
		$this->from = $from;
	}

	public function getUserNewShareMsg() {
		$this->from == 'web' && setcookie('home_up_num', FALSE, time() - 300, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		$lastTid = UserHomePosterTimeline::getLastTid($this->userId);
		$lastTid === FALSE && $lastTid = 0;
		$cacheKey = $this->cacheKey . ":" . $this->userId;
		$cacheHelper = Memcache::instance();
		$tid = $cacheHelper->get($cacheKey);
		if ($this->from == '360') {
			$cacheKey = 'HomeLastTid:' . $this->userId;
			$tid = $cacheHelper->get($cacheKey);
		}
		$oldLastTid = !empty($tid) ? $tid : $lastTid;
		$cacheHelper->set($cacheKey, $oldLastTid, 36000);
        $tids = array();
        $change_size = 0; 
        //如果有更改，检查更改的记录数
        if ($lastTid != $oldLastTid && $lastTid > $oldLastTid) {
			$tids = UserHomePosterTimeline::getTimelineByUid($this->userId, 0, 100);
            foreach ($tids as $key => $value) {
                if ($value == $oldLastTid) {
                    $change_size = $key;
                    break;
                }    
            }
			$change_size = $change_size == 0 ? 100 : $change_size;
		}

		if ($change_size == 100) {
			if (UserHomePosterTimeline::tExists($this->userId, $oldLastTid) == FALSE) {	
				$change_size = 0;
			}
		}
		$this->from == 'web' && setcookie('home_up_num', $change_size, time() + 3000, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		$this->msgInfo = $change_size;
	}

	public function getMsgInfo() {
		return $this->msgInfo;
	}
}
