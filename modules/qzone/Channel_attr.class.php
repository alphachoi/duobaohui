<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneChannel;


class Channel_attr extends \Snake\Libs\Controller {
	
	public function run() {
		if (!$this->_init()) {
			$this->view = array();
			return TRUE;
		}
		$channelHelper = new QzoneChannel();
		$result = $channelHelper->getAttrWords();
		$this->view = $result;
		return TRUE;
	}

	private function _init() {
		return TRUE;
	}
}
