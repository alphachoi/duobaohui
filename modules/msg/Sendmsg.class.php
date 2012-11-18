<?php
/**
 *发私信 
 * gstan
 * author: guoshuaitan@meilishuo.com
 * date 2012-11-08
 */
namespace Snake\Modules\Msg;
use Snake\Package\User\User;
use Snake\Package\User\UserValidate;
use Snake\Package\Msg\Msg;
use Snake\Package\Msg\Helper\RedisUserPrivateMsg;
use Snake\Package\Spam\MaskWords;

class Sendmsg extends \Snake\Libs\Controller {
	private $user_id;
	private $sendto;
	private $msgtext;
	private $checkcode;
	private $reason = 1;//1:成功,2,封禁用户只能给美丽客服精灵发私信,3,传送的数据不全,4:发送的用户不存在,5:有链接,6:有屏蔽词 7:验证码错 8，未登陆
	private $send_id;
	private $cookiekey;

	public function run() {
		if (!$this->_init()) {
			$this->view = array('code' => 200, 'message' => $this->reason);
			return FALSE;
		}
		$userHelper = new User();
		$param['nickname'] = $this->sendto;
		$sendtoUserInfo = $userHelper->getUserProfile($param, 'user_id');
		if (empty($sendtoUserInfo)) {
			$this->reason = 4;
			$this->view = array('code' => 200, 'message' => $this->reason);
			return FALSE;
		}
		$this->send_id = $sendtoUserInfo[0]['user_id'];
		//屏蔽词处理
		$isfilter = $this->filterMsg();	
		if (!$isfilter) {
			$this->view = array('code' => 200, 'message' => $this->reason);
			return FALSE;
		}
		//验证验证吗
		$validate = new UserValidate();
		$istrue = $validate->ValidateCaptcha($this->checkcode, $this->cookiekey);
		if (!$istrue) {
			$this->reason = 7;	
			$this->view = array('code' => 200, 'message' => $this->reason);
			return FALSE;
		}
		//发私信数据
		$msgHelper = new Msg();
		$msgHelper->sendPrivateMsg($this->user_id, $this->send_id, $this->msgtext);
		$this->view = array('code' => 200, 'message' => $this->reason);
	}	

	//屏蔽词
	public function filterMsg() {
		//数组判断可能存在多个域名的情况
		preg_match_all('/www\.(.*?)(\.com|\.cn|\.org|\.net|\.ac|\.biz|\.me)/im', $this->msgtext, $match);
		if (!empty($arr[1])) {
			foreach ($arr[1] as $domain) {
				//只要有外链不可以发
				if (strtolower($domain) != 'meilishuo') {
					$this->reason = 5;
					return false;
				}
			}
		}
		//判断是否含有屏蔽词
		$maskHelper = new MaskWords($this->msgtext);
		$mask = $maskHelper->getMaskWords();
		if (!empty($mask['maskWords'])) {
			$this->reason = 6;	
			return false;
		}
		return true;
	}

	//符合发私信要求
	public function _init() {
		$this->user_id = $this->userSession['user_id'];
		if (empty($this->user_id)) {
			$this->reason = 8;	
			return	FALSE;	
		}
		$level = $this->userSession['level'];
		$status = $this->userSession['is_active'];
		//level为5,6,和未激活用户只能给美丽客服精灵发私信
		if (($level== 5 || $level == 6 || $status == 2) && $pmsg_sendto != '美丽客服精灵') {
			$this->reason = 2;
			return FALSE;
		}
		$this->sendto = trim($this->request->REQUEST['pmsg_sendto']);
		$this->msgtext = trim(strip_tags($this->request->REQUEST['pmsg_text']));
		$this->checkcode = trim($this->request->REQUEST['checkcode']);
		$this->cookiekey = $this->request->COOKIE[DEFAULT_SESSION_NAME];
		if (empty($this->sendto) || empty($this->msgtext) || empty($this->checkcode) || empty($this->cookiekey)) {
			$this->reason = 3;	
			return FALSE;
		}
		return TRUE;
	}
}
