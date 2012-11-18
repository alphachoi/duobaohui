<?php
namespace Snake\Package\User;

USE \Snake\Package\Medal\MedalLib AS MedalLib;

/**
 * 勋章观查者
 * addConnectMedal() 新互联用户
 */
class ObsMedal implements \Snake\Libs\Interfaces\Iobserver {
	
	const WEIBO_BIND = 54;

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Register_actionconnect' :
				$this->addConnectMedal($args['user_id'], $args['reg_from']);	
				break;
			default :
				break;
		}
	}

	/**
	 * 获取勋章类型
	 * @param $type 类型（注册类型/other） 
	 */
	private function getConnectType($type) {
		if (empty($type)) {
			return FALSE;
		}
		$medalType = 0;
		switch ($type) {
			case 3:
				$medalType = self::WEIBO_BIND;
			    break;
			default:
				break;
		}
		return $medalType;
	}
	
	/**
	 * 新互联用户勋章
	 * @param $userId 用户编号
	 * @param $type 互联类型
	 */
	private function addConnectMedal($userId, $type) {
		$medalType = $this->getConnectType($type);	
		if (!empty($medalType)) {
			$medalLibHelper = new MedalLib();
			$medalLibHelper->medalLib($userId);
			$medalLibHelper->addMedalActions($medalType);
		}
	}
}
