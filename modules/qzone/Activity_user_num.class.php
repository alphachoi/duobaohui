<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class Activity_user_num extends \Snake\Libs\Controller {

	private $activityId = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivityHelper = new QzoneActivity();
		$number = $qzoneActivityHelper->getApplyUsersNum($this->activityId);
		$this->view = array(
			'totalNum' => $number 
		);
		return TRUE;
	}

	private function _init() {
		$this->activityId = isset($this->request->REQUEST['activity_id']) ? $this->request->REQUEST['activity_id'] : 0;
		if (empty($this->activityId)) {
			return FALSE;
		}
		return TRUE;	
	}
	
}
