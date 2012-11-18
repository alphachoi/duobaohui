<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;

class Carousel_user extends \Snake\Libs\Controller {

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivityHelper = new QzoneActivity();
		$users = $qzoneActivityHelper->getCarouselUsers();
		$this->view = $users;
		return TRUE;
	}

	private function _init() {
		return TRUE;
	}

}

