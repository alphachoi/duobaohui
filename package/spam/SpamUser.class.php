<?php
namespace Snake\Package\Spam;

Use \Snake\Package\Spam\Helper\DBSpamHelper;

class SpamUser {

	public function getInvalidTime($userId) {
		$sqlData['_uid'] = $userId;
		$spamArr = array();
		$sqlSpam = "select user_id,invalid_time from t_spam_user_log where user_id = :_uid";
		$spamArr = DBSpamHelper::getConn()->read($sqlSpam, $sqlData);
		if (!empty($spamArr)){
			$leftDays = ceil((strtotime($spamArr[0]['invalid_time']) - time())/86400);
			if (intval($leftDays) > 0){
				return intval($leftDays);
			}
			else {
				return -1;
			}
		}
		else {
			return -1;
		}
	}

}
