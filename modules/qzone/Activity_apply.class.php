<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\QzoneActivity;


class Activity_apply extends \Snake\Libs\Controller {
	
	private $data = array();

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$qzoneActivityHelper = new QzoneActivity();
		$params = array(
			'SEASHELL' => $this->request->seashell
		);
		$qzoneActivityList = $qzoneActivityHelper->insertApply($this->data, $params);
		return TRUE;
	}

	private function _init() {
		$this->data['activity_id'] = isset($this->request->REQUEST['activity_id']) ? $this->request->REQUEST['activity_id'] : 0;
		$this->data['user_id'] = isset($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		$this->data['real_name'] = isset($this->request->REQUEST['real_name']) ? $this->request->REQUEST['real_name'] : "";
		$this->data['telephone'] = isset($this->request->REQUEST['telephone']) ? $this->request->REQUEST['telephone'] : 0;
		$this->data['address'] = isset($this->request->REQUEST['address']) ? $this->request->REQUEST['address'] : "";
		$this->data['note'] = isset($this->request->REQUEST['note']) ? $this->request->REQUEST['note'] : "";
		$this->data['reason'] = isset($this->request->REQUEST['reason']) ? $this->request->REQUEST['reason'] : "";
		$this->data['email'] = isset($this->request->REQUEST['email']) ? $this->request->REQUEST['email'] : "";
		$this->data['openid'] = isset($this->request->REQUEST['openid']) ? $this->request->REQUEST['openid'] : "";
		$this->data['img_url'] = isset($this->request->REQUEST['img_url']) ? $this->request->REQUEST['img_url'] : "";
		preg_match("^[-a-zA-Z0-9_.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$^", $this->request->REQUEST['email'], $match);

		if (empty($this->data['activity_id']) || empty($this->data['real_name']) || empty($this->data['telephone']) || 
			empty($this->data['address']) || empty($this->data['reason']) || empty($match)) {
			return FALSE;
		}
		return TRUE;	
	}
	
}
