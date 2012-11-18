<?php
namespace Snake\Modules\Msg;
use Snake\Package\Msg\PrivateMsg;
use Snake\Package\User\User;

class Premsg_talk extends \Snake\Libs\Controller {
	private $user_id;
	private $id;
	public function run() {
		$this->_init();
		//得到这个人当前用户的所有私信
		$msgHelper = new PrivateMsg();
		$colum = 'message_id, from_user_id, to_user_id, message_content, message_time';
		$msgInfo = $msgHelper->getPMsgBoth($this->user_id, $this->id, $colum);
		if (empty($msgInfo)) return false;
		$param = array('user_id', 'nickname', 'avatar_b');
		$userHelper = new User();
		$ids = array($this->user_id, $this->id);
		$userInfo = $userHelper->getUserInfos($ids, $param);
		$info = $this->assmbleInfo($userInfo, $msgInfo);
		$num = count($info);
		$this->view = array('list' => $info, 'totalNum' => $num);
	}

	public function assmbleInfo($userInfo, $msgInfo) {
		foreach($msgInfo as &$info) {
			$info['message_time'] = \Snake\Libs\Base\Utilities::timeStrConverter($info['message_time']);
			if ($info['from_user_id'] == $this->user_id) {
				$info['type'] = 1;
				$info['nickname'] = $userInfo[$this->id]['nickname'];
				$info['avatar'] = $userInfo[$this->user_id]['avatar_b'];
			}	
			else {
				$info['type'] = 2;	
				$info['nickname'] = $userInfo[$this->id]['nickname'];
				$info['avatar'] = $userInfo[$this->id]['avatar_b'];
			}
		}
		return $msgInfo;
	}
	private function _init(){
		$this->id = trim($this->request->path_args[0]);
		if (empty($this->id)) {
			$this->setError(404,400501, 'no parameter');
			return false;
		}
		$this->user_id = $this->userSession['user_id'];
		if (empty($this->user_id)) {
			$this->setError(404, 400402, 'not login in');	
			return false;
		}
	}
}
