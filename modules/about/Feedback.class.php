<?php
namespace Snake\Modules\About;

USE \Snake\Package\About\Feedback AS FeedbackModel;
USE \Snake\Package\User\UserFormat AS UserFormat;

/**
 * 用户反馈或回复，(只接受普通用户)
 * 
 * 用户第一次反馈，没有parent参数,写入一条type=1, flag=0，parent_id=0的数据。
 * 后台管理人员对用户的反馈做出回应。写入一条parent_id=用户第一次反馈的id，flag=1的数据，同时将用户的反馈记录type记为2。
 * 用户在系统消息提供的页面中进行的再次反馈则被视为对管理人员回复的不满意。入一条parent_id=用户第一次反馈的id，flag=0的数据，同时更新第一条反馈type=1。
 * 如果用户反馈的是如'thanks...'等，则需由后台人员手动的关闭对话。置type=2;
 */
class Feedback extends \Snake\Libs\Controller {

	private $userId = 0;
	private $email = '';
	private $title = '';
	private $content = '';	
	private $agent = '';
	private $parentId = 0;

	private $errMsg = 0;

	const EMPTY_EMAIL = 1;
	const EMPTY_CONTENT = 2;
	const EMAIL_INVALID = 3;

	const UNPROCESSED = 1;


	public function run() {
		if (!$this->_init()) {
			$this->view = (int) $this->errMsg;
			return;
		}
		$feedBack = new FeedbackModel();	
		$feedBack->insertFeedback($this->userId, $this->email, $this->content, $this->agent, $this->parentId);
		if (!empty($this->parentId)) {
			$feedBack->updateFeedback($this->parentId, self::UNPROCESSED);		
		}
		$this->view = 0;
		return TRUE;
	}

	private function _init() {
		$this->userId = $this->userSession['user_id'];
		$this->email = trim($this->request->REQUEST['email']);	
		$this->content = trim($this->request->REQUEST['content']);
		$this->agent = $this->request->headers['User-Agent'];
		$this->parentId = empty($this->request->REQUEST['parent']) ? 0 : $this->request->REQUEST['parent'];
		if (empty($this->userId) && empty($this->email)) {
			$this->errMsg = self::EMPTY_EMAIL;
			return FALSE;
		}
		if (empty($this->content)) {
			$this->errMsg = self::EMPTY_CONTENT;
			return FALSE;
		}
		if (!empty($this->email)) {
			$userFormatObj = new UserFormat();
			if ($userFormatObj->emailFormat($this->email) === FALSE) {
				$this->errMsg = self::EMAIL_INVALID;
				return FALSE;
			}
		}
		return TRUE;
	}
}
