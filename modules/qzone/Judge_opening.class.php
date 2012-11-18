<?php
namespace Snake\Modules\Qzone;

class Judge_opening extends \Snake\Libs\Controller {

	public function run() {
		$beginTime = strtotime("2012-9-10");
		$endTime = strtotime("2012-9-11");
		$nowTime = time();
		if ($nowTime > $beginTime && $nowTime < $endTime) {
			$this->view = FALSE;
			return FALSE;			
		}
		$this->view = TRUE;
		return TRUE;
	}


}
	
