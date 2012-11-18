<?php
namespace Snake\Modules\Person;

/**
 * @author Chen Hailong
 * 个人页面，主编的杂志，关注的杂志的数目
 **/

Use \Snake\Package\Manufactory\Poster;
Use \Snake\Libs\Cache\Memcache;
use \Snake\Package\Group\GetUserGroupSquares;   
Use \Snake\Package\Group\GroupUser;
Use \Snake\Package\Group\Helper\RedisUserGroupFollower;
//Use Snake\Libs\Base\ZooClient;

class Poster_group_num extends \Snake\Libs\Controller {
	private $visitedUserId = 0;
	private $userId = 0;
	private $type = NULL;
	private $frame = 0;
	private $page = 0;
	private $cache = TRUE;
	private $limit = 20;
	private $offset = 0;
	private $total = 0;
	private $typeOptions = array('editor', 'follow');
	const maxFrame = FRAME_SIZE_MAX; 
	const pageSize = WIDTH_PAGE_SIZE;
	const isShowClose = 1;
	const isShowLike = 1;


	/**
	 * interface()
	 **/
	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}
		$data = $this->getUserGroupNums();
		if (empty($data)) {
			$this->view = array('totalNum' => 0);
			return FALSE;
		}
		//print_r($data);exit;
		$this->view = array('totalNum' => $data);
		return TRUE;
	}
	
	private function getUserGroupNums() {
		$this->offset = $this->frame + $this->page * self::maxFrame; 
		$result = array();
		//非登陆用户 && cache有返回的
		//$uGHelper = new GetUserGroupSquares();
		if ($this->type === 'editor') {
            $userGroupHelper = new GroupUser();
            $num = (int) $userGroupHelper->getUserGroupNumber($this->visitedUserId, array(0, 1));
		}
		elseif ($this->type === 'follow') {
			$num = RedisUserGroupFollower::getFollowGroupCount($this->visitedUserId);
		}
		return $num;
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
		$userId = !empty($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($userId)) {
			$this->setError(400, 40109, 'user_id is empty');
			return FALSE;
		}
		if (!is_numeric($userId)) {
			$this->setError(400, 40110, 'User id is not a number');
			return FALSE;
		}
		if ($userId < 0) {
			$this->setError(400, 40110, 'User id is nagetive');
			return FALSE;
		}
		$this->visitedUserId = $userId;
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
