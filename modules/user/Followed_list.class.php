<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 得到用户关注列表信息
 *
 */

Use Snake\Package\User\User					     AS User;
Use Snake\Package\User\UserStatistic			 AS UserStatistic;
Use Snake\Package\User\Helper\RedisUserFollow;
Use Snake\Libs\Cache\Memcache;
Use \Snake\Package\Twitter\Twitter;
Use Snake\Package\User\UserFollowList;

class Followed_list extends \Snake\Libs\Controller {
	private $userId = NULL;
	const pageSize = 20;

	public function run() {
        if (!$this->_init()) {
            return FALSE;
        }
		if ($this->visitedUserId == 219) {
			$redirect = BASE_URL . 'goods?frm=fans_219';
			$this->view = $redirect;
			return ;
		}
        $userStatics = UserStatistic::getInstance()->getUserStatistic($this->visitedUserId);
        $num = $userStatics['follower_num'];

        $fansInfo = array();
        $offset = $this->page * self::pageSize;

        $followUser = RedisUserFollow::getFollow($this->visitedUserId, 'DESC', $offset, self::pageSize);
        $followInfo = array();
        foreach($followUser as $key => $user_id) {
            if (RedisUserFollow::isFollowed($user_id, $this->userId)) {
                $followInfo[$key]['friend_show'] = 1;
            }
            else {
                $followInfo[$key]['friend_show'] = 0;
            }
            $followInfo[$key]['follower_id'] = $user_id;
        }
        $result = UserFollowList::getInstance()->createFollowList($followInfo, $this->userId, $this->visitedUserId, 'follower_id', $num); 
		//print_r($result);die;
        if (!empty($result)) {
            $this->view = $result;
        }   
        else {
            $this->view = array();
        }   
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
        if (!$this->setUserId()) {
            return FALSE;
        }
        return TRUE;
    }

    private function setUserId() {
        $this->userId = $this->userSession['user_id']; //7580696;
        return TRUE;
    }

    private function setVisitedUserId() {
        $visitorId = !empty($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
        if (empty($visitorId)) {
            $this->setError(400, 40109, 'user_id is empty');
            return FALSE;
        }
        if (!is_numeric($visitorId)) {
            $this->setError(400, 40110, 'User id is not a number');
            return FALSE;
        }
        if ($wordId < 0) {
            $this->setError(400, 40110, 'User id is nagetive');
            return FALSE;
        }
        $this->visitedUserId = $visitorId;
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
