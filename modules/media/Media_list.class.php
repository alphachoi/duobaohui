<?php
namespace Snake\Modules\Media;

/**
 * 关于我们页新闻报道数据
 * @author yishuliu@meilishuo.com
 * @since 2012-08-02
 * @version 1.0
 */

Use \Snake\Package\Media\Media;			
Use \Snake\Libs\Cache\Memcache;

class Media_list extends \Snake\Libs\Controller {
	private $visitedUserId = NULL;
	private $cache = 0; //TRUE;
	private $limit = 30;
	private $totalNum = 0;
	const maxSizePerPage = 30;

	public function run()  {
        if (!$this->_init()) {
            return FALSE;
        }
		$mediaInfo = $this->_getMediaInfo();
		if (empty($mediaInfo)) {
			$this->view = array();
			return FALSE;
		}
		//print_r(array('info' => $mediaInfo, 'totalNum' => count($mediaInfo)));die;
		$this->view = array('info' => $mediaInfo, 'totalNum' => $this->totalNum);
		return TRUE;
	}
	
	private function _init() {
        if (!$this->setPage()) {
            $this->setError(400, 20150, 'page invalid');
            return FALSE;
        }
        if (!$this->setLimit()) {
            return FALSE;
        }
		return TRUE;
		//current login userId
		$this->visitedUserId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($this->visitedUserId)) {
			$this->setError(400, 40101, 'userId is empty');
			return FALSE;
		}
		return $this->visitedUserId;
	}
	
	/**
	 *	cache handle
	 **/
	private function _getMediaInfo() {
		$this->offset = $this->page * self::maxSizePerPage;
		$cacheKey = 'Media:media';
		$cacheHelper = Memcache::instance();
		$mediaInfo = $cacheHelper->get($cacheKey);
		if ($this->cache && !empty($mediaInfo)) {
			return $mediaInfo;
		}
		$mediaHelper = new Media();
		$mediaInfo = $mediaHelper->getMedia($this->offset, $this->limit);
		if (empty($mediaInfo)) {
			//$this->setError(400, 40104, 'user is not exist or user medal is empty');
			return FALSE;
		}
		foreach ($mediaInfo as $key => $value) {
			if (isset($value['time'])) {
				$mediaInfo[$key]['time'] = date('Y-m-d', strtotime($mediaInfo[$key]['time']));
			}
		}
		$this->totalNum = $mediaHelper->getNumOfMedia();
		$cacheHelper->set($cacheKey, $mediaInfo, 3600);
		return $mediaInfo;
	}

    private function setPage() {
        $page = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
        if (!is_numeric($page) || $page < 0) {
            return FALSE;
        }   
        $this->page = (int)$page;
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
}
