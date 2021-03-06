<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class Set_user_isfans extends \Snake\Libs\Controller {
	
	private $number = NULL;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivity = new QzoneActivity();
		$params = array(
			'SEASHELL' => $this->request->seashell
		);
		$result = $qzoneActivity->checkUserIsFans($params);
		$this->view = $result;
		return TRUE;
	}

	private function _init() {
		$this->number = isset($this->request->REQUEST['number']) ? $this->request->REQUEST['number'] : 0;
		return TRUE;
	}

}
