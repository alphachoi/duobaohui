<?php
namespace Snake\Modules\Goods;

/**
 * @author yishuliu@meilishuo.com
 * 个人页面，喜欢和分享的海报数据
 **/
 
Use \Snake\Package\Manufactory\Poster;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Package\Twitter\Twitter;

class Share_self extends \Snake\Libs\Controller {

	private $visitedUserId = 0;
	private $userId = 0;
	private $type = NULL;
	private $frame = 0;
	private $page = 0;
	private $cache = TRUE;
	private $limit = 20;
	private $offset = 0;
	private $total = 0;
	private $tids = array();
    private $tidFilter = 0;
	private $typeOptions = array('share', 'like');
	const maxFrame = FRAME_SIZE_MAX; //6
	const pageSize = WIDTH_PAGE_SIZE; //20
	const isShowClose = 1;
	const isShowLike = 1;
	const isShowPrice = 0;

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
		$this->offset = ($this->frame + $this->page * self::maxFrame) * self::pageSize; 
		$cacheHelper = Memcache::instance();
		$md5 = md5("{$this->visitedUserId}_{$this->offset}_{$this->limit}_{$this->type}_{$this->tidFilter}");
		$cacheKey = "share_self:{$md5}";
		$response = $cacheHelper->get($cacheKey);

		//非登陆用户 && cache有返回的
		if ($this->cache && empty($this->userId) && !empty($response)) {
			return $response;
		}
		else {
			$posterObj = new Poster();
			if ($this->type == 'like') {
				$this->getUserLikeTids();
				$posterObj->isShowLike(self::isShowLike);
			}
			elseif ($this->type == 'share') {
				$this->getUserShareTids();
				if ($this->userId == $this->visitedUserId) {
					$posterObj->isShowClose(self::isShowClose);
				}
				else {
					$posterObj->isShowLike(self::isShowLike);
				}
			}
			$posterObj->isShowPrice(self::isShowPrice);
			$posterObj->setVariables($this->tids, $this->userId);
			$poster	= $posterObj->getPoster();

			$response = array('goods' => array('tInfo' => $poster, 'totalNum' => $this->total));

			if (empty($this->userId)) {
				$cacheHelper->set($cacheKey, $response, 600);
			}
		}
		//print_r($response);die;
		return $response;
	}

	private function getUserLikeTids() {
        $cacheHelper = Memcache::instance();
		if ($this->offset + $this->limit < 120) {
        	$data = $cacheHelper->get('person:share_data' . $this->visitedUserId);
			if (!empty($data['data'])) {
				$this->total = (int) $data['total'];
				$tids = array_splice($data['data'], $this->offset, $this->limit);	
				$this->tids = $tids;
				return $tids;
			}	
		}
        $client = ZooClient::getClient();
        $data = $client->user_likes_twitters($this->visitedUserId, $this->offset, $this->limit);
		if (!empty($data['data'])) {
			$this->total = (int) $data['total'];
			$tids = $data['data'];  //array_splice($data['data'], $this->offset, $this->limit);	
		}
		$this->tids = $tids;
		
	}
	
	private function getUserShareTids() {
		$tObj = new Twitter();
		$this->total = (int) $tObj->getNumOfTwitterByUid($this->visitedUserId);
		$result = $tObj->getPicTwitterByUid($this->visitedUserId, $this->offset, $this->limit);
		if (empty($result)) {
			return FALSE;
		}
		foreach ($result as $key => $value) {
            if ($value == $this->tidFilter) {
                continue;
            }
			$this->tids[] = $value;
		}
		//$this->total = count($tObj->getPicTwitterByUid($this->visitedUserId, 0, 10000));
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
        if (!$this->setTidFilter()) {
            return FALSE;    
        }
		return TRUE;
	}

    private function setTidFilter() {
        $this->tidFilter = (int)$this->request->REQUEST['tid'];
        return TRUE;    
    }

	private function setUserId() {
		$this->userId = $this->userSession['user_id']; //7580696;
		return TRUE;
	}

	private function setType() {
		$type = !empty($this->request->REQUEST['twitter']) ? $this->request->REQUEST['twitter'] : '';
		if (!in_array($type, $this->typeOptions)) {
			$this->setError(400, 40112, 'type is illegal');
			return FALSE;
		}
		$this->type = $type;
		return TRUE;
	}

	private function setLimit() {
		$wordId = !empty($this->request->REQUEST['limit']) ? (int)$this->request->REQUEST['limit'] : $this->limit;
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
		$wordId = !empty($this->request->REQUEST['user_id']) ? (int)$this->request->REQUEST['user_id'] : 0;

		if (empty($wordId)) {

			$twitterId = !empty($this->request->REQUEST['tid']) ? (int)$this->request->REQUEST['tid'] : 0;

			if (!empty($twitterId)) {
				$fields = array('twitter_id', 'twitter_author_uid');
				$tids = array($twitterId);
				$twitterHelper = new Twitter($fields, array()); 
				$tinfo = $twitterHelper->getTwitterByTids($tids);
				$wordId = $tinfo[0]['twitter_author_uid'];
			}
		}
		if (empty($wordId) || $wordId < 0) {
			$this->setError(400, 400, 'illeage id in share' . $wordId);
		}

		$this->visitedUserId = $wordId;
		return TRUE;
	}

	private function setFrame() {
		$frame = !empty($this->request->REQUEST['frame']) ? (int)$this->request->REQUEST['frame'] : 0;
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
		$page = !empty($this->request->REQUEST['page']) ? (int)$this->request->REQUEST['page'] : 0;
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
