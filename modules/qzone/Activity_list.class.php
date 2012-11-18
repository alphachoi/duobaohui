<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class Activity_list extends \Snake\Libs\Controller {

	private $start = 0;
	private $limit = 5;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivityHelper = new QzoneActivity();
		$offset = $this->start * $this->limit;
		$params = array(
			'SEASHELL' => $this->request->seashell
		);
		$qzoneActivityList = $qzoneActivityHelper->getActivitiesInfo($offset, $this->limit, $params);
		$qzoneActivityNum = $qzoneActivityHelper->getActivitiesNumber();
		//$qzoneActivityTotalData = $qzoneActivityHelper->getTotalApplyData();

		$this->view = array(
			'activityInfo' => $qzoneActivityList,
			'totalNum'	=> $qzoneActivityNum,
			'totalPrice' => 53605,//$qzoneActivityTotalData['total_price'],
			'userTotalNum' => 130//$qzoneActivityTotalData['total_num']
		);
		return TRUE;
	}

	private function _init() {
		$this->start = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		return TRUE;	
	}
	
}
