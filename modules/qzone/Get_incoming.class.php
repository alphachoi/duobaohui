<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class Get_incoming extends \Snake\Libs\Controller {
	
	public function run(){
		$qzoneActivityHelper = new QzoneActivity();
		$activityList = $qzoneActivityHelper->getInComingActivities();
		if (empty($activityList)) {
			$activityList = "";
		}
		$this->view = $activityList;
		return TRUE;
	}

}
