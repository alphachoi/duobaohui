<?php
namespace Snake\Modules\User;

use \Snake\Package\User\UserRelation;
use \Snake\Package\User\Helper\RedisUserFollow;
use \Snake\Package\User\UserStatistic;
use \Snake\Package\Timeline\Timeline;
use \Snake\Package\Msg\UpdateUserNotice;
use \Snake\Package\User\ChangeRedisUserFans;
use \Snake\Package\User\ChangeUserFollowGroup;

class Cancel_follow extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {
	
	private $userId = 0;
	private $otherUserIds = array();
	private $_observers = array();

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		foreach ($this->otherUserIds as $uid) {
			if ($this->userId == $uid || empty($uid)) {
                $this->setError(400, 40207, 'can not follow youself or uid is empty, uid is ' . $uid);
                return FALSE;
            }
			$return = UserRelation::getInstance()->setUserCancelFollow($this->userId, $uid);
			if (!empty($return)) {
				//关注，粉丝列表	
				$this->addObserver(new ChangeRedisUserFans());				
				//统计
				$this->addObserver(new UserStatistic());
				//更新海报墙
				$this->addObserver(new Timeline());
				//取消关注主编杂志社
				$this->addObserver(new ChangeUserFollowGroup());

				foreach ($this->observers as $obs) {
                    $obs->onChanged('CancelFollow', array(
                        'user_id' => $this->userId,
                        'other_id' => $uid,
                    ));
                }
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
		$this->otherUserIds = $fuidArray;
		return TRUE;	
	}

    public function addObserver($observer) {
        $this->observers[] = $observer;
    }
}
