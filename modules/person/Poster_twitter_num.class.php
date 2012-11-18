<?php
namespace Snake\Modules\Person;

/**
 * @author Chen Hailong
 * 个人页面，喜欢和分享的海报数目
 **/
 
Use \Snake\Package\Manufactory\Poster;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Package\Twitter\Twitter;

class Poster_twitter_num extends \Snake\Libs\Controller {

	private $visitedUserId = 0;
	private $userId = 0;
	private $type = NULL;
	private $frame = 0;
	private $page = 0;
	private $cache = 0; //TRUE;
	private $limit = 20;
	private $offset = 0;
	private $total = 0;
	private $tids = array();
	private $typeOptions = array('share', 'like');
	const maxFrame = FRAME_SIZE_MAX; 
	const pageSize = WIDTH_PAGE_SIZE;
	const isShowClose = 1;
	const isShowLike = 1;
	const isShowPrice = 1;


	/**
	 * interface()
	 **/
	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
		$data = $this->getUserTids();
		//print_r($data);exit;
		if (empty($data)) {
			$data = array();
		}
		$this->view = $data;
		return TRUE;
	}
	
	private function getUserTids() {
		$this->offset = $this->frame + $this->page * self::maxFrame; 
		$cacheHelper = Memcache::instance();
		$md5 = md5("{$this->visitedUserId}_{$offset}_{$this->limit}_{$this->type}");
		$cacheKey = "person:{$md5}";
		$response = $cacheHelper->get($cacheKey);

		//非登陆用户 && cache有返回的
		if ($this->cache && empty($this->userId) && !empty($response)) {
			return $response;
		}
		else {
			if ($this->type === 'like') {
				$this->getUserLikeTids();
			}
			elseif ($this->type == 'share') {
				$this->getUserShareTids();
			}
			$response = array('totalNum' => $this->total);

			if (empty($this->userId)) {
				$cacheHelper->set($cacheKey, $response, 600);
			}
		}
		return $response;
	}

	private function getUserLikeTids() {
        $cacheHelper = Memcache::instance();
		if ($this->offset + $this->limit < 120) {
        	$data = $cacheHelper->get('person:share_data' . $this->visitedUserId);
			if (!empty($data['data'])) {
				$this->total = (int) $data['total'];
				return TRUE;
			}	
		}
        $client = ZooClient::getClient();
        $data = $client->user_likes_twitters($this->visitedUserId, $this->offset, $this->limit);
		if (!empty($data['data'])) {
			$this->total = (int) $data['total'];
		}
	}
	
	//TODO
	private function getUserShareTids() {
		$tObj = new Twitter();
		$result = $tObj->getPicTwitterByUid($this->visitedUserId, $this->offset, $this->limit);
		foreach ($result as $key => $value) {
			$this->tids[$key] = $result[$key]['twitter_id'];
		}
		$this->total = (int) $tObj->getNumOfTwitterByUid($this->visitedUserId);
	}

	/**
	 * 初始化变量
	 **/
	private function _init() {
		if (!$this->setVisitedUserId()) {
			return FALSE;
		}
		if (!$this->setPage()) {
			return FALSE;
		}
		if (!$this->setFrame()) {
			return FALSE;
		}
		if (!$this->setUserId()) {
			return FALSE;
		}
		if (!$this->setType()) {
			return FALSE;
		}
		if (!$this->setLimit()) {
			return FALSE;
		}
		return TRUE;
	}

	private function setUserId() {
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	private function setType() {
		$type = !empty($this->request->REQUEST['type']) ? $this->request->REQUEST['type'] : '';
		if (!in_array($type, $this->typeOptions)) {
			$this->setError(400, 40112, 'type is illegal');
			return FALSE;
		}
		$this->type = $type;
		return TRUE;
	}

	private function setLimit() {
		$wordId = !empty($this->request->REQUEST['limit']) ? $this->request->REQUEST['limit'] : $this->limit;
		if (empty($wordId)) {
			$this->setError(400, 40103, 'limit is illeage, limit:' . $wordId);
			return FALSE;
		}
		if (!is_numeric($wordId) || $wordId < 0) {
			$this->setError(400, 40103, 'limit is illeage, limit:' . $wordId);
			return FALSE;
		}
		$this->limit = $wordId;
		return TRUE;
	}

	private function setVisitedUserId() {
		$wordId = !empty($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($wordId)) {
			$this->setError(400, 40109, 'user_id is empty');
			return FALSE;
		}
		if (!is_numeric($wordId)) {
			$this->setError(400, 40110, 'User id is not a number');
			return FALSE;
		}
		if ($wordId < 0) {
			$this->setError(400, 40110, 'User id is nagetive');
			return FALSE;
		}
		$this->visitedUserId = $wordId;
		return TRUE;
	}

	private function setFrame() {
		$frame = !empty($this->request->REQUEST['frame']) ? $this->request->REQUEST['frame'] : 0;
		if (!is_numeric($frame)) {
			$this->setError(400, 40105, 'bad frame');
			return FALSE;
		}
		$frame = (int) $frame;
		if ($frame < 0 || $frame >= FRAME_SIZE_MAX) {
			$this->setError(400, 40106, 'out of range');
			return FALSE;
		}
		$this->frame = $frame;
		return TRUE;

	}

	private function setPage() {
		$page = !empty($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		if (!is_numeric($page)) {
			$this->setError(400, 40107, 'bad page');
			return FALSE;
		}
		if ($page < 0)  {
			$this->setError(400, 40108, 'page is nagetive');
			return FALSE;
		}
		$this->page = $page;
		return TRUE;
	}

}
