<?php
namespace Snake\Modules\Home;

/**
 * 我的首页海报墙总数
 *
 **/

Use \Snake\Package\Home\HomePoster				AS HomePoster;
Use \Snake\Package\Manufactory\Poster			AS Poster;
use \Snake\libs\Cache\Memcache                  AS Memcache;
Use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;
Use \Snake\Package\Timeline\Timeline            AS Timeline;
Use \Snake\Package\Timeline\TimelineDB          AS TimelineDB;

class Home_poster_num extends \Snake\Libs\Controller {

    private $userId = NULL;
    
    public function run() {
		if (!$this->_init()) {
            return FALSE;   
        }

        //重建timeline,以后会迁移到登录
		$this->rebuildTimeline();	

		$homePosterObj = new HomePoster();
		$data = array();
		$data['totalNum'] = $homePosterObj->getTotalNum($this->userId); 
        $this->view = $data;
		return TRUE;
    }   

    private function _init() {
        if (!$this->setUserId()) {
			$this->setError(400, 20150, 'empty user_id!');
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

	private function rebuildTimeline() {
        if (UserHomePosterTimeline::exists($this->userId) == FALSE || UserHomePosterTimeline::getSize($this->userId) < 50) {
            $result = TimelineDB::getInstance()->getTimelineFromDB($this->userId, "/*Home-rebuild gc*/ last_tid, last_update_time, tids");
            $lastTid = 0;
            $dbTids = array();
            $lastUpdateTime = 0;
            if (!empty($result)) {
                $lastTid = $result[0]['last_tid'];
                $lastUpdateTime = strtotime($result[0]['last_update_time']);
                $dbTids = explode(',', $result[0]['tids']);
            }   
			$log = new \Snake\Libs\Base\SnakeLog('rebuild_timeline', 'normal');
			$log->w_log(print_r(array('uid' => $this->userId, 'lasttid' => $lastTid, 'lastTime' => $lastUpdateTime, 'dbTids' => $dbTids), true));
            Timeline::rebuildUserHomePosterTimelineNew($this->userId, $lastTid, $lastUpdateTime, $dbTids);
        }   
    } 
}
