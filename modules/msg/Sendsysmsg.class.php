<?php
/**
 * 发系统消息
 */
namespace Snake\Modules\Msg;
use Snake\Package\Msg\SystemMsg;
use Snake\Package\Msg\Msg;

class Sendsysmsg extends \Snake\Libs\Controller {
	/**
	 * param type 系统消息类别 type = 1为全站系统消息 
	 * type = 2 为某些用户发送系统消息
	 */
	public function run() {
		$sys_type = $_REQUEST['type'];	
		if ($sys_type == 1) {
			$back = $this->sendForAll();
		}
		else {
			$back = $this->sendForSome();	
		}
		if ($back) $this->view = array('code' => 200, 'message' => '发送系统消息成功');
		else       $this->view = array('code' => 400, 'message' => '发送系统消息失败');
	}
	//全站系统消息
	private function sendForAll() {
		$content = $_REQUEST['message_content'];
		if(empty($content)) return false;
		$send_user_id = !empty($_REQUEST['send_user_id']) ? $_REQUEST['send_user_id'] : 219 ;
		$msgHelper = new SystemMsg();
		$back = $msgHelper->insertSysMsgForAll($content, $send_user_id);
		return $back;
	}
	//某些用户
	private function sendForSome() {
		$content = $_REQUEST['message_content'];
		if(empty($content)) return false;
		$to_user_id = $_REQUEST['to_user_id'];
		if (empty($to_user_id)) return false;
		$to_user_id = explode(',',  $to_user_id);
		$msgHelper = new Msg();
		foreach($to_user_id as $user_id) {
			$msgHelper->sendSysMsg($user_id , $content);
		}
		return true;
	}
}
