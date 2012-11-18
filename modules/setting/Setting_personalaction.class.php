<?php
namespace Snake\Modules\Setting;

USE \Snake\Package\User\User as User;
USE \Snake\Package\Session\UserSession AS UserSession;

class Setting_personalaction extends \Snake\Libs\Controller {

	private $userId = NULL;

	private $baseInfo = array(
		'user_id' => NULL,
		'realname' => '',
		'nickname' => '',
	);
	private $extInfo = array(
		'user_id' => NULL,
		'gender' => '女',
		'birthday' => '0000-00-00',
		'province_id' => 0,
		'city_id' => 0,
		'school' => '',
		'industry' => '',
		'hobby' => '',
		'weibo_url' => '',
		'about_me' => '',
	);

	public function run() {

		if (!$this->_init()) {
			return FALSE;
		}
		$user = new User();
		$user->updateUserInfo($this->baseInfo);
		$user->updateUserExtInfo($this->extInfo);

		$params = array(
            'nickname',
            'realname',
            'gender',
            'birthday',
            'province_id',
            'city_id',
            'school',
            'industry',
            'hobby',
            'weibo_url',
            'about_me',
        );
        $info = $user->getUserInfo($this->userId, $params);
		UserSession::updateSessionData($info, $this->request->COOKIE);

        $this->view = array(
            'info' => TRUE,
        );
	}

	private function _init() {
        if (!$this->setUserId()) {
            $this->setError(400, 20150, 'empty user_id!');
            return FALSE;
        }

		if (empty($this->request->REQUEST['nickname']) || empty($this->request->REQUEST['gender'])) {
				$this->setError(400, 40150, 'empty parameter');
				return FALSE;
		}	

		$this->baseInfo['user_id'] = $this->userId; 
		$this->baseInfo['nickname'] = trim($this->request->REQUEST['nickname']);	
		$this->baseInfo['realname'] = trim($this->request->REQUEST['realname']);	

		$this->extInfo['user_id'] = $this->userId;
		$this->extInfo['gender'] = $this->request->REQUEST['gender'];
		$this->extInfo['birthday'] = $this->request->REQUEST['birthday'];
		$this->extInfo['province_id'] = $this->request->REQUEST['province_id'];
		$this->extInfo['city_id'] = $this->request->REQUEST['city_id'];
		$this->extInfo['school'] = $this->request->REQUEST['school'];
		$this->extInfo['industry'] = $this->request->REQUEST['industry'];
		$this->extInfo['hobby'] = $this->request->REQUEST['hobby'];
		$this->extInfo['weibo_url'] = $this->request->REQUEST['weibo_url'];
		$this->extInfo['about_me'] = $this->request->REQUEST['about_me'];

		return TRUE;
	}

    private function setUserId() {
        $this->userId = $this->userSession['user_id'];
        if (empty($this->userId)) {
            return FALSE;
        }
        return TRUE;
    }
}
