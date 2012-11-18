<?php
namespace Snake\Modules\Home;

/**
 * @author yishuliu@meilishuo.com
 * 我的首页白名单用户查看用户海报墙
 *
 **/

Use \Snake\Package\Home\HomePoster				AS HomePoster;
Use \Snake\Package\Manufactory\Poster			AS Poster;
use \Snake\libs\Cache\Memcache                  AS Memcache;

class Home_track_timeline extends \Snake\Libs\Controller {

    private $userId = NULL;
	private $id = NULL;
	private $frame = 0;
    private $page = 0;
	const maxFrame = FRAME_SIZE_MAX; 
    const pageSize = WIDTH_PAGE_SIZE;
    const isShowClose = 1;
    const isShowLike = 1;
	const isShowComment = 3;
    
    public function run() {
		//$this->userId = 765;
		//$this->id = 765;
		//$this->page = 0;
		//$this->frame = 0;

		if (!$this->_init()) {
            return FALSE;   
        }

		$offset = $this->frame + $this->page * self::maxFrame;
		
		$homePosterObj = new HomePoster($this->page, $this->frame, $offset, self::pageSize);
		$result = $homePosterObj->trackTimeline($this->userId, $this->id);
		$tids = $result['tids'];
		$totalNum = $result['totalNum'];
		$bannerInfo = $result['banner'];
		$alertWindow = $result['alertWindow'];
		$params = $result['params'];

		if (empty($tids)) {
			self::setError(400, 40021, 'empty tids!');
			$responsePosterData = array('tInfo' => FALSE, 'totalNum' => FALSE, 'banner' => FALSE, 'alertWindow' => FALSE, 'params' => FALSE);
			$this->view = $responsePosterData;
			return TRUE;
		}

		$cacheHelper = Memcache::instance();
		$tidsCache = md5(implode(',', $tids));
        $cacheKeyForPosters = "Home:Home_trackTimeline:{$tidsCache}";
        $responsePosterData = $cacheHelper->get($cacheKeyForPosters);

		if (!empty($responsePosterData)) {
        	$this->view = $responsePosterData;
			return TRUE;
		}
		else {
			$posterObj = new Poster();
			$posterObj->isShowLike(self::isShowLike);
			$posterObj->isShowClose(self::isShowClose);
			$posterObj->isShowComment(self::isShowComment);
			$posterObj->setVariables($tids, $this->userId);
			$poster = $posterObj->getPoster();
			$responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum, 'alertWindow' => $alertWindow, 'params' => $params);
			//TODO 当全部迁移到snake上后，需要将cache增大
            $cacheHelper->set($cacheKeyForPosters, $responsePosterData, 600);
			$responsePosterData['banner'] = $bannerInfo;
		}  
		//print_r($responsePosterData);die;
        $this->view = $responsePosterData;
		return TRUE;
    }   

    private function _init() {
        if (!$this->setUserId()) {
			$this->errorMessage(400, 'empty user_id!');
            return FALSE;
        }   
		if (!$this->setPage()) {
            return FALSE;
        }
        if (!$this->setFrame()) {
            return FALSE;
        }
		if (!$this->setCheckId()) {
			return FALSE;
		}
    }   

    private function setUserId() {
        $this->userId = $this->userSession['user_id'];
		if (empty($this->userId)) {
			return FALSE;
		}
        return TRUE;
    }    
    
    private function getUserId() {
        return $userId;
	}
	
	private function setCheckId() {
		$id = isset($this->request->REQUEST['uid']) ? $this->request->REQUEST['uid'] : 0;
		if (!is_numeric($id)) {
            $this->errorMessage(400, 'error id');
            return FALSE;
		}
		$this->id = $id;
		return TRUE;
	}

	private function setFrame() {
        $frame = isset($this->request->REQUEST['frame']) ? $this->request->REQUEST['frame'] : 0;
        if (!is_numeric($frame)) {
            $this->errorMessage(400, 'bad frame');
            return FALSE;
        }
        if ($frame < 0 || $frame >= FRAME_SIZE_MAX) {
            $this->errorMessage(400, 'out of frame');
            return FALSE;
        }
        $this->frame = $frame;
        return TRUE;
    }

    private function setPage() {
        $page = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
        if (!is_numeric($page)) {
            $this->errorMessage(400, 'bad page');
            return FALSE;
        }
        if ($page < 0) {
            $this->errorMessage(400, 'page is negative');
            return FALSE;
        }
        $this->page = $page;
        return TRUE;
    }

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
        //$this->head  = 400;
        //$this->view  = array('code' => $code, 'message' => $message);
        return TRUE;
    }   
}
