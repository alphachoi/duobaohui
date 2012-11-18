<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;

class Activity_num extends \Snake\Libs\Controller {

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivityHelper = new QzoneActivity();
		$num = $qzoneActivityHelper->getActivitiesNumber();
		$this->view = array(
			'totalNum' => $num
		);
		return TRUE;
	}

	private function _init() {
		return TRUE;
	}

}
