<?php

namespace Snake\Package\Qzone;

use \Snake\Package\Qzone\Channel\QzoneAttribute;

class QzoneChannel {
	
	public function getAttrWords() {
		$helper = new QzoneAttribute();
		$helper->setQzoneType();
		$helper->sortType();
		$helper->setQzoneAttribute();
		$helper->assembleQzoneAttribute();
		$result = $helper->getData();
		return $result;
	}	

}
