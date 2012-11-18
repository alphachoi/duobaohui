<?php
namespace Snake\Package\User;

USE \Snake\Package\Connect\ConnectFactory       AS ConnectFactory;

class ObsUpdateRedisConnect implements \Snake\Libs\Interfaces\Iobserver {

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Register_actionconnect' :
				$this->updateRedisConnect($args['type'], $args['user_id'], $args['access_token'], $args['auth'], $args['ttl']);	
				break;
			default :
				break;
		}
	}

	/**
	 * 更新新用户互联Redis信息
	 * @param $type 互联类型
	 * @param $userId 用户编号
	 * @param $access token
	 * @param $auth auth
	 * @param $ttl 过期时间
	 */
	private function updateRedisConnect($type, $userId, $access, $auth, $ttl) {
		if (empty($type) || empty($userId) || empty($access) || empty($auth)) {
			return FALSE;
		}
		$connectFactory = new ConnectFactory();
		$connectFactory->UpdateToken($type, $userId, $access, $auth, $ttl);		
	}
}
