<?php
namespace Snake\Modules\Home;

Use \Snake\Package\Home\HomePoster              AS HomePoster;
Use \Snake\Package\Manufactory\Poster           AS Poster;
use \Snake\libs\Cache\Memcache                  AS Memcache;
use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;

class Home_message_num extends \Snake\Libs\Controller {

	//用户编号
	private $userId = NULL;
	//用户已看到的最新的tid
	private $oldLastTid = 0;
	// 是否为首页第一页
	private $page = 0;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}	
		$lastTid = UserHomePosterTimeline::getLastTid($this->userId);
		$changeSize = 0;
		if ($lastTid != $this->oldLastTid) {
			$tids = UserHomePosterTimeline::getTimelineByUid($this->userId, 0, 100);
			foreach ($tids as $key => $value) {
                if ($value == $this->oldLastTid) {
                    $changeSize = $key;
                    break;
                }
            }
            $changeSize = $changeSize == 0 ? 100 : $changeSize;		
		}

		if ($changeSize == 100) {
			if (UserHomePosterTimeline::tExists($this->userId, $this->oldLastTid) == FALSE) {
				$changeSize = 0;
			}
		}

		//由重构页面跳入到非重构的黄闪闪，重构完毕后去掉
		setcookie('home_up_num', $changeSize, time() + 3000, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);

		$tids = array();
		$changeSize > 0 && $tids = UserHomePosterTimeline::getTimelineByUid($this->userId, 0, $changeSize);	

		$this->view = array(
			'change_size' => $changeSize,
			'last_tid' => $lastTid,
			'tids' => $tids,
		);
		return TRUE;
	}

	private function _init() {
        if (!$this->setUserId()) {
            $this->setError(400, 20150, 'empty user_id!');
            return FALSE;
        }
        if (!$this->setOldLastTid()) {
            $this->setError(400, 20150, 'last_tid invalid');
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

    private function setOldLastTid() {
        $cacheHelper = Memcache::instance();
		$cacheKey = $this->request->COOKIE['santorini_mm'] . ':' . $this->userId;
		$cacheKey360 = 'HomeLastTid:' . $this->userId;
		$page = empty($this->request->REQUEST['page']) ? 0 : $this->request->REQUEST['page'];
		$oldLastTid = $cacheHelper->get($cacheKey);	
		//首次请求或由别页面跳入首页
		if (empty($oldLastTid) || ($page == 0 && empty($this->request->REQUEST['old_last_tid']))) {
			$oldLastTid = UserHomePosterTimeline::getLastTid($this->userId);
			$cacheHelper->set($cacheKey, $oldLastTid, 36000);
			$cacheHelper->set($cacheKey360, $oldLastTid, 36000);
		}
        $this->oldLastTid = (int) $oldLastTid;
        return TRUE;
    }
}
