<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\SearchGoods;
Use Snake\Package\Goods\Tag;
Use Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache;

class Search_totalnum extends \Snake\Libs\Controller {
	private $wordName = '';
	private $frame = 0;
	private $page = 0;
	private $offset = 0;
	private $userId = 0;
	/**
	 * 不用cache
	 */
	private $useCacheForPosters = FALSE;

	const pageSize = 20;
	const maxFrame = 6;
	const isShowClose = 0;
	const isShowLike = 1;
	


	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

        $maskWords = new \Snake\Package\Spam\MaskWords($this->wordName);
        $mask = $maskWords->getMaskWords();
//        $tt = print_r($mask, TRUE);
//        $log = new \Snake\Libs\Base\SnakeLog('mask_test_zx', 'normal');
//        $log->w_log($tt);
        if (!empty($mask['maskWords'])) {
            $this->view = array('totalNum' => 0, 'showNum' => 0);
			return TRUE;
        }

		$this->offset = $this->frame + $this->page * self::maxFrame; 
		$searchHelper = new SearchGoods();
		$searchHelper->setWordName($this->wordName);
		$searchHelper->setOffset($this->offset);
		$searchHelper->setPageSize(self::pageSize);
		if (!$searchHelper->dataProcess()) {
			if (empty($responsePosterData)) {
				$responsePosterData = array('totalNum' => 0, 'showNum' => 0);
			}
			$this->view = $responsePosterData;
			return TRUE;
		}

		$totalNum = $searchHelper->getTotalNum();
		$showNum = $searchHelper->getShowNum();
		$responsePosterData = array("totalNum" => 0, 'showNum' => 0);
		if (!empty($totalNum)) {
			$responsePosterData['totalNum'] = $totalNum;
			$responsePosterData['showNum'] = $showNum;
		}
		$this->view = $responsePosterData;
		return TRUE;
	}
	

	private function _init() {
		if (!$this->setWordName()) {
			return FALSE;
		}
		if (!$this->setFrame()) {
			return FALSE;
		}
		if	(!$this->setPage()) {
			return FALSE;
		}
		$this->setUserId();
		return TRUE;
	}

	private function setWordName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (trim($wordName) === '') {
			return FALSE;
		}
		if (!empty($wordName)) {
			$this->wordName = $wordName;	
		}
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	private function setFrame() {
		$frame = intval($this->request->REQUEST['frame']);
		if (!isset( $frame) || !is_numeric($frame)) {
			self::setError(400, 400, 'bad frame');
			return FALSE;
		}
		if ($frame < 0 || $frame >= FRAME_SIZE_MAX) {
			self::setError(400, 400, 'out of frame');
			return FALSE;
		}
		$this->frame = $frame;
		return TRUE;
	}

	private function setPage() {
		$page = intval($this->request->REQUEST['page']);
		if (!isset($page) || !is_numeric($page)) {
			self::setError(400, 400, 'bad page');
			return FALSE;
		}
		if ($page < 0)  {
			self::setError(400, 400, 'page is negative');
			return FALSE;
		}
		$this->page = $page;
		return TRUE;
	}








}
