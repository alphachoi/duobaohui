<?php
namespace Snake\Package\Spam;

Use \Snake\Package\User\User;

class ReportUser {

	public function allowReport($uid) {
		if (empty($uid)) {
			return 0;
		}
		$limit = 5000000;
		if ($uid <= $limit) {
			return 1;
		}
		else {
			$user = new User();
			$info = $user->checkUserProperty($uid);
			$result = 0;
			foreach ($info as $v) {
				$result = $result || $v;
			}
			return $result;
		}
	}

}
