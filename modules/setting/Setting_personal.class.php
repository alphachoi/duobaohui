<?php
namespace Snake\Modules\Setting;

USE \Snake\Package\User\User as User;

class Setting_personal extends \Snake\Libs\Controller {
	
	private $userId = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}	
		$user = new User();		
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

		$this->view = array(
			'info' => $info,
		);
	}

	private function _init() {
		if (!$this->setUserId()) {
			$this->setError(400, 20150, 'empty user_id!');
			return FALSE;
		}
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
