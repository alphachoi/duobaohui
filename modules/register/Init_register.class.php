<?php
namespace Snake\Modules\Register;

USE \Snake\Package\Session\UserSession AS UserSession;
USE \Snake\Package\User\LogOut AS LogOut;

/**
 * 注册第一步
 *
 * 退出登录
 * after: 显示注册页面
 * 
 * @author ChaoGuo
 */
class Init_register extends \Snake\Libs\Controller {

	public function run() {

		if (!empty($this->userSession['user_id'])) {
			$logOut = new LogOut($this->userSession['user_id']);
			$logOut->userLogOut($this->request->COOKIE);
		}

		$this->view = array('status' => TRUE);
	}
}
