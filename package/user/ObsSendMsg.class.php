<?php
namespace Snake\Package\User;

USE \Snake\Package\Msg\Msg AS Msg;
USE \Snake\Package\Msg\Helper\RedisUserPrivateMsg AS RedisUserPrivateMsg;

/**
 * 发送系统消息
 *
 * addConnectMsg() 新互联用户
 * addRegisterMsg() 普通注册
 *
 */
class ObsSendMsg implements \Snake\Libs\Interfaces\Iobserver {
	
	const CONNECT = 'Register_actionconnect';
	const REGISTER = 'Register_action';
	const ACTIVATE = 'Activate';

	const SYSMSG = 1;
	const NOSYSMSG = 0;

	public function onChanged($sender, $args) {
		switch ($sender) {
		case self::CONNECT :
				$this->addConnectMsg($args['user_id']);	
				break;
		case self::REGISTER :
				$this->addRegisterMsg($args['user_id'], $args['other_id'], $args['nickname'], $args['invitename']);
				break;
		case self::ACTIVATE :
				$this->addActivateMsg($args['user_id']);
				break;
			default :
				break;
		}
	}

	/**
	 * 向新互联用户发送系统消息
	 * @param $userId 接收系统消息用户编号
	 */
	private function addConnectMsg($userId) {
		$msgHelper = new Msg();
		$msgInfo = $this->getMsgInfo(self::CONNECT);	
		if (!empty($msgInfo) && !empty($userId)) {
			$msgHelper->sendSysMsg($userId, $msgInfo);	
		}
	}

	/**
	 * 向邀请人发送私信
	 * @param $userId 被邀请人
	 * @param $otherId 邀请人
	 * @param $nickName 被邀请人的呢称
	 * @param $inviteName 邀请人的呢称
	 */
	private function addRegisterMsg($userId, $otherId, $nickName, $inviteName) {
		if (empty($userId) || empty($otherId) || empty($nickName) || empty($inviteName)) {
			return FALSE;
		}
		$msgHelper = new Msg();
		$params = array('invite_name' => $inviteName, 'nickname' => $nickName, 'user_id' => $userId);
		$msgInfo = $this->getMsgInfo(self::REGISTER, $params);	
		if (!empty($msgInfo)) {
			$msgHelper->sendPrivateMsg($userId, $otherId, $msgInfo);
		}
	}

	/**
	 * 注册发送系统消息
	 * @param $userId 接收系统消息用户编号
	 */
	private function addActivateMsg($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		$msgInfo = $this->getMsgInfo(self::ACTIVATE);
		if (!empty($msgInfo)) {
			$msgHelper = new Msg();
			$msgHelper->sendSysMsg($userId, $msgInfo);	
		}
	}

	private function getMsgInfo($type, $param = array()) {
		// TODO 
		$pictureUrl = 'http://img.meilishuo.com';
		$initMsg = '';
		switch ($type) {
			case self::CONNECT :
			case self::ACTIVATE :
				$initMsg = "<img src='{$pictureUrl}/css/images/register.jpg' />";	
				break;
			case self::REGISTER :
				$initMsg = '亲爱的' . $param['invite_name'] . 
					'：<br/>你邀请的<a href="http://www.meilishuo.com/person/u/' . 
					$param['user_id'].'" target="_blank">' . $param['nickname'] . 
					'</a>注册了美丽说，并且关注了你！你可以通过互动帮助她更快的熟悉美丽说哦！';
				break;
			default :
				break;
		}

		return $initMsg;
	}
}
