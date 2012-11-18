<?php
namespace Snake\Modules\Msg;
use Snake\Package\Msg\SystemMsg;
use Snake\Package\Msg\PrivateMsg;

class  Delete_msg extends \Snake\Libs\Controller {
	
	private $reason = 1;//1为正常,其他都是异常，2:未登陆，3，没有message_id 4,更新失败 5，怎么删除的
	private $type = 0;//删除的类型
	private $user_id;
	private $message_id;

	public function run() {
		if (!$this->_init()) {
			$this->view = array('code' => 200, 'message' => $this->reason);	
			return FALSE;
		}
		//全站系统消息删除
		if ($this->type == 1) {
			$sHelper = new SystemMsg();
			$back = $sHelper->deleteTotalMsg($this->message_id, $this->user_id);
			if (empty($back)) $this->reason = 4;
		}
		else {
			$pHelper = new PrivateMsg();	
			$back = $pHelper->deleteOneMsg($this->message_id, $this->user_id);
			if (empty($back)) $this->reason = 5;
		}
		$this->view = array('code' => 200, 'message' => $this->reason);
	}	
	
	private function _init() {
		$this->user_id = $this->userSession['user_id'];
		if (empty($this->user_id)) {
			$this->reason = 2;	
			return	FALSE;	
		}
		$this->message_id = $this->request->REQUEST['message_id'];
		if (empty($this->message_id)) {
			$this->reason = 3;	
			return FALSE;
		}
		if (!empty($this->request->REQUEST['type'])) {
			$this->type = 1;	
		}
		return TRUE;
	}
	
	
	
	
}
