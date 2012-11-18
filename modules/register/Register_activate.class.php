<?php
namespace Snake\Modules\Register;

USE \Snake\Package\User\User AS User;
USE \Snake\libs\Cache\Memcache AS Memcache;
USE \Snake\Package\Session\UserSession AS UserSession;
USE \Snake\package\user\LogOnOB AS LogOnOB;
USE \Snake\Package\User\ObsSendMsg AS ObsSendMsg;


/**
 * 注册激活动作
 * before : 用户点击激活链接
 * after : 跳转至选择喜欢分类界面
 *
 * @author ChaoGuo
 */
class Register_activate extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {
	
	//激活码
	private $activateCode = '';
	//用户编号
	private $userId = NULL;

	//返回的标记位
	private $errMsg = 0;

	private $alreadyActived = 0;

	const ALREADY_ACTIVATED = 1; //已经激活
	const NOT_EXISTS = 2;        //不存在
	const STATUS_ERROR = 3;      //封禁用户
	const UNKNOW_ERROR = 4; 
	const DB_ERROR = 5;

	private $observers = array();

	public function run() {
		if (!$this->_init()) {
			if (empty($this->errMsg)) {
				return FALSE;
			}
			else {
				$this->view = $this->errMsg;
				return TRUE;
			}
		}
		
		if (!$this->registerActivate()) {
			$this->view = self::DB_ERROR;
			return FALSE;
		}
		$this->view = $this->errMsg;
		return TRUE;
	}

	private function registerActivate() {
		$user = new User();
		$result = $user->activateUser($this->activateCode);
		if ($result === FALSE) {
			return FALSE;
		}
		else {
			$this->userId = $result['user_id'];
			$this->addObserver(new LogOnOB());
			if (empty($this->alreadyActived)) {
				$this->addObserver(new ObsSendMsg());
			}
		}

		foreach ($this->observers as $obs) {
			$obs->onChanged('Activate', array(
				'info' => $result,
				'request' => $this->request,	
				'user_id' => $this->userId,
			));
		}
		return TRUE;
	}

	private function _init() {
		if (!$this->setActivateCode()) {
			return FALSE;
		}
		if (!$this->checkStatus()) {
			return FALSE;
		}	
		return TRUE;
	}

	private function setActivateCode() {
		if (empty($this->request->REQUEST['activecode'])) {
			$this->setError(400, 20100, 'empty activate code');
			return FALSE;
		}
		$this->activateCode = $this->request->REQUEST['activecode'];
		return TRUE;
	}

	private function checkStatus() {
		$user = new User();
		$result = $user->getActivedByCode($this->activateCode, TRUE);
		if ($result === FALSE) {
			$this->errMsg = self::NOT_EXISTS;
			return FALSE;
		}
		if ($result == 2 || $result == 0 || $result == 1) {
			if ($result == 1) {
				$this->alreadyActived = 1;
			}
			return TRUE;
		}
		elseif ($result == -2) {
			$this->errMsg = self::STATUS_ERROR;
			return FALSE;
		}
		else {
			$this->errMsg = self::UNKNOW_ERROR;
			return FALSE;
		}
	}

    public function addObserver($observer) {
        $this->observers[] = $observer;
	}   	
}
