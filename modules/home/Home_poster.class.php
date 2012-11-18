<?php
namespace Snake\Modules\Home;

/**
 * 我的首页海报墙数据流
 * 
 * @author ChaoGuo
 **/

Use \Snake\Package\Home\HomePoster				AS HomePoster;
Use \Snake\Package\Manufactory\Poster			AS Poster;
Use \Snake\libs\Cache\Memcache                  AS Memcache;
Use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;
Use \Snake\Package\Timeline\Timeline            AS Timeline;
Use \Snake\Package\Timeline\TimelineDB			AS TimelineDB;

class Home_poster extends \Snake\Libs\Controller {

	//用户编号
    private $userId = 0;
	//当前帧
	private $frame = 0;	
    //当前页
    private $page = 0;
	//用户已知最新tid
	private $oldLastTid = 0;
	
    const isShowClose = 0;
    const isShowLike = 1;
    const isShowComment = 3;
	const isShowPrice = 1;
    
    public function run() {
	
		if (!$this->_init()) {
            return FALSE;   
        }

		$offset = WIDTH_PAGE_SIZE * (FRAME_SIZE_MAX * $this->page + $this->frame);
		$homePoster = new HomePoster($this->page, $this->frame, $offset, WIDTH_PAGE_SIZE, $this->request);
		$result = $homePoster->getHomePostersByUid($this->userId);
		$tids = $result['tids'];
		if (!empty($result['param'])) {
			$this->oldLastTid = $result['param']['total_last_tid'];
		}

		$continue = 0;
		$poster = array();
		if (!empty($tids)) {
			$posterObj = new Poster();
			$posterObj->isShowLike(self::isShowLike);
			$posterObj->isShowClose(self::isShowClose);
			$posterObj->isShowComment(self::isShowComment);
			$posterObj->isShowPrice(self::isShowPrice);
			$posterObj->setVariables($tids, $this->userId);
			$poster = $posterObj->getPoster();
			if (empty($poster)) {
				$logTids = implode(',', $tids);
				$storageLog = new \Snake\Libs\Base\SnakeLog('gc_twitter_storage', 'normal');
				$storageLog->w_log(print_r(array('uid' => $this->userId, 'page' => $this->page, 'frame' => $this->frame, 'tids' => $logTids), true));
				//数据被删除或storage出现问题。
				$continue = 1;
			}
		}
		$this->view = array(
			'tInfo' => $poster,
			'totalNum' => $result['total'], 
			'continue' => $continue,
		);
		return TRUE;
    }

    private function _init() {
        if (!$this->setUserId()) {
			$this->setError(400, 20150, 'empty user_id!');
            return FALSE;
		} 
		if (!$this->setPage()) {
			$this->setError(400, 20150, 'page invalid');
            return FALSE;
        }
        if (!$this->setFrame()) {
			$this->setError(400, 20150, 'frame invalid');
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
    
	private function setFrame() {
        $frame = isset($this->request->REQUEST['frame']) ? $this->request->REQUEST['frame'] : 0;
		if (!is_numeric($frame) || $frame < 0 || $frame >= FRAME_SIZE_MAX) {
			return FALSE;
		}
        $this->frame = (int)$frame;
        return TRUE;

    }

    private function setPage() {
        $page = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		if (!is_numeric($page) || $page < 0) {
			return FALSE;
		}
        $this->page = (int)$page;
        return TRUE;
    }
}
