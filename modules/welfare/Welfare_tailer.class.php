<?php
namespace Snake\Modules\Welfare;
use Snake\Package\Welfare\Welfare;
use  \Snake\Package\Picture\PictureConvert;
class Welfare_tailer extends \Snake\Libs\Controller {
	public function run() {
		$this->welfareHelper = Welfare::getInstance();
		$colum = "activity_id,title,products_preview_img,begin_time,sortno,trynumber,products_price";
		$info = $this->welfareHelper->getTrailerWelfare($colum, 2);
		if (empty($info)) {
			$this->view = array();
			return false;
		}
		foreach ($info as &$value){	
			$pictureHelper = new PictureConvert($value['products_preview_img']);
			$value['products_preview_img'] = $pictureHelper->getPictureO();
		}
		$this->view = $info;
			
	}	
	
}

