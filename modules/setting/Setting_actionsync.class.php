<?php
namespace Snake\Modules\Setting;

USE \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnect;
USE \Snake\Package\User\UserConnect AS UserConnect;

class Setting_actionsync extends \Snake\Libs\Controller {

	private $userId = NULL;

	//同步到新浪微博
	private $sinaSyncGoods = 0;
	//同步到Qzone
	private $qzoneSyncGoods = 0;

	//Q+
	private $qplusSync = array(
		'qplusShare' => 0,  //分享宝贝
		'qplusCollect' => 0,//收集宝贝
		'qplusLike' => 0,//喜欢宝贝
		'qplusCreate' => 0,//创建杂志社
		'qplusFollow' => 0,//关注杂志社
	);

	public function run() {

		if (!$this->init()) {
			return FALSE;
		}

		$userConnectHelper = new UserConnect();

		$weiboSetting = array(
			'sync_goods' => 0,
			'sync_medal' => 0,
			'sync_collect' => 0,
			'sync_like' => 0,
			'sync_ask' => 0,
			'sync_answer' => 0,
		);		
		$weiboSetting['sync_goods'] = $this->sinaSyncGoods;
		if ($this->weiboSetting['sync_goods'] == 1) {
			$param = array(
				'user_id' => $this->userId,
				'user_type' => 3,
			);
			$preAuth = $userConnectHelper->getUserConnectFromDB($param, 'access');
			if (empty($preAuth)) {
				$this->setError(400, 40150, 'empty access');
				return FALSE;
			}
			$preAuth = $preAuth[0]['access'];
			UserConnect::updateUserToken('weibo', $this->userId, '7776000', $preAuth);
		}

		RedisUserConnect::setUserSetting('weibo', $this->userId, json_encode($weiboSetting));
		$userConnectHelper->updateUserConnectSettings($this->userId, 3, $weiboSetting);

		$weiboSetting['sync_goods'] = $this->qzoneSyncGoods;
		RedisUserConnect::setUserSetting('qzone', $this->userId, json_encode($weiboSetting));	
		$userConnectHelper->updateUserConnectSettings($this->userId, 4, $weiboSetting);

		$weiboSetting['sync_goods'] = 0;
		$weiboSetting['sync_medal'] = $this->qplusSync['qplusShare'];
		$weiboSetting['sync_collect'] = $this->qplusSync['qplusCollect'];
		$weiboSetting['sync_like'] = $this->qplusSync['qplusLike'];
		$weiboSetting['sync_ask'] = $this->qplusSync['qplusCreate'];
		$weiboSetting['sync_answer'] = $this->qplusSync['qplusFollow'];

		RedisUserConnect::setUserSetting('qplus', $this->userId, json_encode($weiboSetting));
		$userConnectHelper->updateUserConnectSettings($this->userId, 11, $weiboSetting);

		$this->view = '保存成功';
	}

	private function _init() {
		if (!$this->setUserId()) {
			return FALSE;
		}	
		$this->setSync();
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

	private function setSync() {
		if (isset($this->request->REQUEST['sina_sync_goods'])) {
			$this->sinaSyncGoods = 1;
		}
		if (isset($this->request->REQUEST['qzone_sync_goods'])) {
			$this->qzoneSyncGoods = 1;
		}
		if (isset($this->request->REQUEST['qplus_share'])) {
			$this->qplusSync['qplusShare'] = 1;
		}
		if (isset($this->request->REQUEST['qplus_collect'])) {
			$this->qplusSync['qplusCollect'] = 1;
		}
		if (isset($this->request->REQUEST['qplus_like'])) {
			$this->qplusSync['qplusLike'] = 1;
		}
		if (isset($this->request->REQUEST['qplus_create'])) {
			$this->qplusSync['qplusCreate'] = 1;
		}	
		if (isset($this->request->REQUEST['qplus_follow'])) {
			$this->qplusSync['qplusFollow'] = 1;
		}
	}

}
