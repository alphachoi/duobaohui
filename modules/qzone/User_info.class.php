<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class User_info extends \Snake\Libs\Controller {

	private $openId = NULL;
	private $openKey = NULL;
	private $activityId =NULL;
	
	public function run() {
		$qzoneActivityHelper = new QzoneActivity();
		$params = array(
			'SEASHELL' => $this->request->seashell
		);
		if (!$this->_init()) {
			$userInfo = $qzoneActivityHelper->getUserInfoSecondTime($params, $this->activityId);
		}
		else {
			$userInfo = $qzoneActivityHelper->getUserInfoFirstTime($this->openId, $this->openKey, $params, $this->activityId);
		}
			$this->view = $userInfo;
			return TRUE;

		
	}

	private function _init() {
		$this->openId = isset($this->request->REQUEST['openid']) ? $this->request->REQUEST['openid'] : 0;
		$this->openKey = isset($this->request->REQUEST['openkey']) ? $this->request->REQUEST['openkey'] : 0;
		$this->activityId = isset($this->request->REQUEST['activity_id']) ? $this->request->REQUEST['activity_id'] : 0;
		if (empty($this->openId) && empty($this->openKey)) {
			return FALSE;
		}
		return TRUE;
	}

}
