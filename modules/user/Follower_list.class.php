<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 得到粉丝列表信息
 *
 **/

Use Snake\Package\User\User					     AS User;
Use Snake\Package\User\UserStatistic			 AS UserStatistic;
Use Snake\Package\User\UserFollowList;
Use Snake\Package\User\Helper\RedisUserFans;
Use Snake\Libs\Cache\Memcache;
Use \Snake\Package\Twitter\Twitter;

class Follower_list extends \Snake\Libs\Controller {
	private $userId = NULL;
	const pageSize = 20;

	public function run()  {
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
        $fansUser = RedisUserFans::getFans($this->visitedUserId, 'DESC', $offset, self::pageSize);
        foreach($fansUser as $key => $user_id) {
            $fansInfo[$key]['user_id'] = $user_id;
            if (RedisUserFans::isFans($user_id, $this->userId)) {
				//是否互相关注
                $fansInfo[$key]['friend_show'] = 1;
            }   
            else {
                $fansInfo[$key]['friend_show'] = 0;
            }
        }
		$result = UserFollowList::getInstance()->createFollowList($fansInfo, $this->userId, $this->visitedUserId, 'user_id', $num);
		//print_r($result);die;
		if (!empty($result)) {
			$this->view = $result;
		}
		else {
			$this->view = array();
		}
		return TRUE;
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
