<?php
namespace Snake\Modules\User;

USE \Snake\Package\User\Helper\RedisUserFollow;
USE \Snake\Libs\Cache\Memcache;
USE \Snake\Package\User\UserStatistic;
USE \Snake\Package\Timeline\Timeline;
USE \Snake\Package\User\UserRelation;
USE \Snake\Package\Msg\Msg;
USE \Snake\Package\Msg\Alert;
USE \Snake\Package\Msg\UpdateUserNotice;
USE \Snake\Package\User\ChangeRedisUserFans;
USE \Snake\Package\User\ChangeUserFollowGroup;

class Follow extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {

	private $userId = 0;
	private $observers = array();
	private $otherUserIds = array();

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$gKey = $this->userSession['gkey'];	
		$globalKey = $this->request->COOKIE['MEILISHUO_GLOBAL_KEY'];	

		if ($gKey != $globalKey) {
			$this->view = array('status' => 2);
			return FALSE;	
		}

        $cache = Memcache::instance();
        $maxUserId = $cache->get('max_user_id');
        //这个地方可以优化
        foreach ($this->otherUserIds as $uid) {
			$this->observers = array();
            if ($this->userId == $uid || empty($uid)) {
            	$this->setError(400, 40207, 'can not follow youself or uid is empty, uid is ' . $uid);
                return FALSE;
            }
			if (!is_numeric($uid)) {
				$this->setError(400, 40207, 'fuid invalid');
				return FALSE;
			}
            if (!empty($maxUserId) && $uid > $maxUserId) {
            	$this->setError(400, 40206, 'can not follow future user');
                return FALSE;
            }

			//关注的人不许超过600
			$followerNum = RedisUserFollow::getFollowNumber($this->userId);

			if ($followerNum >= 600 && $followerNum <= 650) {
				$this->setError(400, 40204, 'touch the top 600 followers');
				return FALSE;
			}
			$return = UserRelation::getInstance()->setUserFollow($this->userId, $uid);

            if (!empty($return)) {
				//关注，粉丝列表
				$this->addObserver(new ChangeRedisUserFans());
				//统计
				$this->addObserver(new UserStatistic()); 
				//关注人动态
				$this->addObserver(new UpdateUserNotice());
				//关注主编杂志社
				$this->addObserver(new ChangeUserFollowGroup());
				//更新海报墙 
				$this->addObserver(new Timeline());
				//添加提醒
				$this->addObserver(new Alert());

				foreach ($this->observers as $obs) {
					$obs->onChanged('Follow', array(
						'user_id' => $this->userId,
					    'other_id' => $uid,	
						'userSession' => $this->userSession,
						'alert' => 'fans_num',
					)); 
        		}	
            }
			else {
				continue;
				//$this->setError(400, 40150, 'already followed');
				//return FALSE;
			}
        }
		$this->view = array('status' => 1);
	}
	
    private function _init() {
        if (!$this->setUserId()) {
            return FALSE;
        }   
        if (!$this->setOtherUserIds()) {
            return FALSE;
        }   
        return TRUE;
    }  

    private function setUserId() {
		if (empty($this->userSession['user_id'])) {
			$this->setError(400, 40201, 'Please login first');
			return FALSE;
		}
        if (intval($this->userSession['level']) === 5) {
            $this->setError(400, 40205, 'This account is blocked by anti-spam, user_id: ' . $this->userId);
            return FALSE;
        }
        $this->userId = $this->userSession['user_id'];
        return TRUE;
    }

    private function setOtherUserIds() {
        $fuid = !empty($this->request->REQUEST['fuid']) ? $this->request->REQUEST['fuid'] : '';
        if (empty($fuid)) {
            $this->setError(400, 40202, 'fuid can not be empty');
            return FALSE;
        }
		
		$fuidArray = explode(':', $fuid);
        if (empty($fuidArray)) {
            $this->setError(400, 40202, 'fuid can not be empty');
            return FALSE;
        }
        if (count($fuidArray) > 8) {
            $this->setError(400, 40203, 'can not follow more than 8 users at once!');
            return FALSE;
        }

        $this->otherUserIds = $fuidArray;
        return TRUE;
    }
	
	public function addObserver($observer) {
        $this->observers[] = $observer;
    } 
}
