<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class Activity_single extends \Snake\Libs\Controller {

	private $activityId = NULL;
	private $start = 0;
	private $limit = 10;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivityHelper = new QzoneActivity();
		$params = array(
			'SEASHELL' => $this->request->seashell
		);
		$offset = $this->start * $this->limit;
		$qzoneActivityInfo = $qzoneActivityHelper->getActivityInfo($this->activityId, $this->start, $this->limit, $params);
		$this->view = $qzoneActivityInfo;
		return TRUE;
	}

	private function _init() {
		$this->activityId = isset($this->request->REQUEST['activity_id']) ? $this->request->REQUEST['activity_id'] : 0;
		if (empty($this->activityId)) {
			return FALSE;
		}
		$this->start = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		$this->limit = isset($this->request->REQUEST['limit']) ? $this->request->REQUEST['limit'] : 10;
		return TRUE;	
	}
	
}
