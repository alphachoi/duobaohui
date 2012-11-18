<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 得到用户个人信息
 *
 **/

Use Snake\Package\User\User                      AS User;

class Count_user_heart_num extends \Snake\Libs\Controller {
	
	public function run() {
		$userId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		$user = new User();
		$userInfo = $user->getUserStatistic($userId);
		$heart_num = (!empty($userInfo['heart_num']) && $userInfo['heart_num'] > 0) ? $userInfo['heart_num'] : 0;
		$this->view = $heart_num;
	}
}
