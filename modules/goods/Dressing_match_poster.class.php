<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\MatchShow;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\Tag;
Use Snake\Package\Manufactory\Poster;

class Dressing_match_poster extends \Snake\Libs\Controller {
	private $frame = 0;
	private $page = 0;
	private $offset = 0;
	private $useCache = TRUE;
	private $userId = 0;
	const pageSize = WIDTH_PAGE_SIZE;
	const maxFrame = FRAME_SIZE_MAX;
	const isShowClose = 0;
	const isShowLike = 1;

	public function run () {
		if (!$this->_init()) {
			return FALSE;	
		}
		$this->offset = $this->frame + self::maxFrame * $this->page; 

		$cacheHelper = Memcache::instance();
		$cacheKeyForPosters = "CacheKey:dressing_match_poster:{$this->frame}_{$this->page}";
		$responsePosterData = $cacheHelper->get($cacheKeyForPosters);
		$useCache = $this->useCache && empty($this->userId) && !empty($responsePosterData);
		if (!$useCache) {
			$matchShow = new MatchShow($this->offset, self::pageSize);	
			$tids = $matchShow->getTids();
			$totalNum = $matchShow->getTotalNum();

			$posterObj = new Poster();
			$posterObj->isShowLike(self::isShowLike);
			$posterObj->isShowClose(self::isShowClose);
			$posterObj->setVariables($tids, $this->userId);
			$poster	= $posterObj->getPoster();
			$poster = Tag::addTagWzz($poster, $this->frame, $this->page);
			$responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum);
			if (!empty($responsePosterData['tInfo']) && empty($this->userId)) {
				$cacheHelper->set($cacheKeyForPosters, $responsePosterData, 600);
			}
		}
		$this->view = $responsePosterData;
		return TRUE;
	}

	private function _init() {
		if (!$this->setFrame()) {
			return FALSE;	
		}
		if (!$this->setPage()) {
			return FALSE;
		}
		$this->setUserId();
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	private function setFrame() {
		$frame = intval($this->request->REQUEST['frame']);
		if (!isset( $frame) || !is_numeric($frame)) {
			self::setError(400, 400, 'error frame');
			return FALSE;
		}
		if ($frame < 0 || $frame >= FRAME_SIZE_MAX) {
			self::setError(400, 400, 'out of frame');
			return FALSE;
		}
		$this->frame = $frame;
		return TRUE;
	}

	private function setPage () {
		$page = intval($this->request->REQUEST['page']);
		if (!isset($page) || !is_numeric($page)) {
			self::setError(400, 400, 'bad page');
			return FALSE;
		}
		if ($page < 0)  {
			self::setError(400, 400, 'page error');
			return FALSE;
		}
		$this->page = $page;
		return TRUE;
	}

}
