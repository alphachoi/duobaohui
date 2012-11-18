<?php
namespace Snake\Modules\Home;

/**
 * 我的首页动态请求海报
 * 
 * @author ChaoGuo
 **/

Use \Snake\Package\Home\HomePoster				AS HomePoster;
Use \Snake\Package\Manufactory\Poster			AS Poster;
Use \Snake\libs\Cache\Memcache                  AS Memcache;
Use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;

class Home_message extends \Snake\Libs\Controller {

	//用户编号
    private $userId = 0;
	//新海报数量
	private $lastTid = 0;

	private $tids = array();

	const isShowClose = 0;
    const isShowLike = 1;
    const isShowComment = 3;
	const isShowPrice = 1;

    public function run() {
		if (!$this->_init()) {
            return FALSE;   
        }

		setcookie('home_up_num', FALSE, time() - 300, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);

		if (!empty($this->tids)) {
			$messageTids = $this->tids;
			$this->lastTid = $messageTids[0];
			$poster = new Poster();
			$poster->isShowLike(self::isShowLike);
			$poster->isShowClose(self::isShowClose);
			$poster->isShowPrice(self::isShowPrice);
			$poster->isShowComment(self::isShowComment);
			$poster->setVariables($messageTids, $this->userId);
			$posterInfo = $poster->getPoster();
		}

		$this->view = array(
			'tInfo' => $posterInfo,
			'last_tid' => $this->lastTid,
		);
		return TRUE;
    }

    private function _init() {
        if (!$this->setUserId()) {
			$this->setError(400, 20150, 'empty user_id!');
            return FALSE;
        }   
		if (!$this->setTids()) {
			$this->setError(400, 20150, 'empty tids');
			return FALSE;
		}
		if (!$this->setLastTid()) {
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

	private function setTids() {
		if (!empty($this->request->REQUEST['tids'])) {
			$this->tids = explode(',', $this->request->REQUEST['tids']);
		}
		return TRUE;
	}

	private function setLastTid() {
		$lastTid = !empty($this->request->REQUEST['last_tid']) ? $this->request->REQUEST['last_tid'] : UserHomePosterTimeline::getLastTid($this->userId); 
		if (!is_numeric($lastTid)) {
			return FALSE;
		}
		$cacheKey = $this->request->COOKIE['santorini_mm'] . ':' . $this->userId;
		$cacheHelper = Memcache::instance();
		$cacheHelper->set($cacheKey, (int) $lastTid, 36000);
		return TRUE;
	}
}
