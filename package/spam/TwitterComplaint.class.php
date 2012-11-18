<?php
namespace Snake\Package\Spam;

use \Snake\Package\Spam\Helper\DBTwitterComplaint;

class TwitterComplaint {

	private $user = array();
	private $tid;
	private $type;

	public function __construct($user, $tid, $type) {
		$this->user = $user;
		$this->tid = $tid;
		$this->type = $type;
	}

	public function twitterComlpaint() {
		if(!$this->checkParam()) {
			return 400;
		}
		if (!$this->checkAuthority()) {
			return 401;
		}
		$this->save();
		return 200;
	}

	private function checkParam() {
		if (empty($this->user) || 
			empty($this->tid) ||
			empty($this->type) ||
			!in_array($this->type, array(1, 2, 3, 4))) {
			return FALSE;
		}
		return TRUE;
	}

	private function checkAuthority() {
		if (!empty($this->user['isjb'])) {
			return TRUE;
		}
		return FALSE;
	}

	private function save() {
		$sql = "insert into t_walrus_spam_reportTwitter (user_id, twitter_id, report_time, report_type) values ({$this->user['user_id']}, {$this->tid}, {$_SERVER['REQUEST_TIME']}, {$this->type})";
		DBTwitterComplaint::getConn()->write($sql);
	}

}	
