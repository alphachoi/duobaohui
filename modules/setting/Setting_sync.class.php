<?php
namespace Snake\Modules\Setting;

USE \Snake\Package\User\Helper\RedisUserConnectHelper AS UserConnect;

/**
 * 显示用户绑定页面
 * 查询用户互联绑定情况
 */
class Setting_sync extends \Snake\Libs\Controller {
	
	private $userId = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		
		$outsites = array('weibo', 'qzone', 'txweibo');
		$sync_settings = array();
		foreach ($outsites as $outsite) {
			$exists = $this->_checkAuthToken($outsite, $this->userId);
			$sync_settings[$outsite] = $exists;
		}

		$this->view = array(
			'info' => $sync_settings,
		);
		var_dump($this->view);
		return TRUE;
	}

	private function _init() {
		if (!$this->setUserId()) {
			return FALSE;
		}
		return TRUE;
	}	

	private function setUserId() {
		if (empty($this->userSession['user_id'])) {
			$this->setError(400, 40150, 'empty user_id');
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

	private function _checkAuthToken($outsite, $user_id) {
		$exists = array(
			'connect' => 'off',
			'share' => 'off',
		);
		$auth = UserConnect::getUserAuth($outsite, $user_id);
		if (!empty($auth)) {
			$exists['connect'] = 'on';
		}
		//如果过期，自动解除绑定
		$token = UserConnect::getUserToken($outsite, $user_id);
		if (!empty($token)) {
			$flag = UserConnect::getUserSetting($outsite, $user_id);
			$flag = json_decode($flag, TRUE);
			if (empty($flag)) {
				$exists['share'] = 'on';
			}	
			else {
				$exists['share'] = !empty($flag['sync_goods']) ? 'on' : 'off';
			}
			if ($outsite == 'qzone') {
				$result = UserConnect::getUserSetting('qplus', $user_id);
				$result = json_decode($result, TRUE);
				if (empty($result)) {
					$exists['qplusShare'] = 'on'; //分享宝贝
                    $exists['qplusCollect'] = 'on'; //收集宝贝
                    $exists['qplusLike'] = 'on';  //喜欢宝贝
                    $exists['qplusCreate'] = 'on';  //创建杂志社
                    $exists['qplusFollow'] = 'on';  //关注杂志社
				}
				else {
                    $exists['qplusShare'] = !empty($result['sync_medal']) ? 'on' : 'off'; //分享宝贝
                    $exists['qplusCollect'] = !empty($result['sync_collect']) ? 'on' : 'off'; //收集宝贝
                    $exists['qplusLike'] = !empty($result['sync_like']) ? 'on' : 'off';  //喜欢宝贝
                    $exists['qplusCreate'] = !empty($result['sync_ask']) ? 'on' : 'off';  //创建杂志社
                    $exists['qplusFollow'] = !empty($result['sync_answer']) ? 'on' : 'off';  //关注杂志社
                }    
			}
		}
		return $exists;
	}
}
