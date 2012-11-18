<?php
namespace Snake\Modules\Group;

Use Snake\Package\Group\HandleSearch;
Use Snake\Package\Goods\Tag;
Use Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache;

class Search_poster extends \Snake\Libs\Controller {
    private $searchKey = '';
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

    public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$searchHelper = new HandleSearch();
		$offset = ($this->page * self::maxFrame + $this->frame) * self::pageSize;
		$result = $searchHelper->handleSearchGroup($this->searchKey, $this->userId, $offset, self::pageSize);
		$this->view = $result;
		return TRUE;
	}

	private function _init() {
		$this->searchKey = isset($this->request->REQUEST['word_name']) ? $this->request->REQUEST['word_name'] : "";
		if(trim($this->searchKey) === '') {
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		$this->frame = isset($this->request->REQUEST['frame']) ? $this->request->REQUEST['frame'] : 0;
		if ($this->frame > self::maxFrame) {
			return FALSE;
		}
		$this->page = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		return TRUE;
	}
}
