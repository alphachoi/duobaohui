<?php
namespace Snake\Modules\Msg;
Use Snake\Package\Msg\Msg;
Use Snake\Package\Msg\SystemMsg;

class Clear_alert extends \Snake\Libs\Controller {
	private $user_id;
	private $params;
	public function run() {
		$check = $this->_init();	
		if (!$check)  return false;
		$msgHelper = new Msg();
		$sysHelper = new SystemMsg();
		//传多种参数，一下清楚各种提醒
		if (is_array($this->params)) {
			foreach($this->params as $param) {
				$msgHelper->setZeroByParamAndUid($param, $this->user_id);
				if ($param == "sysmesg") {
					$sysgHelper->setSysZero($this->user_id, 2);		
				}
			}
		}
		else {
			$msgHelper->setZeroByParamAndUid($this->params, $this->user_id);
			if ($param == "sysmesg") {
				$sysgHelper->setSysZero($this->user_id, 2);		
			}
		}
	}

	public function _init() {
		$this->user_id = $this->userSession['user_id'];
		if (empty($this->user_id)) {
			$this->setError(404,400402, "no login in");
			return false;
		}
		$this->params = $this->request->REQUEST['msgParams'];
		if (empty($this->user_id)) {
			$this->setError(404,400405, "no parameter");	
			return false;
		}
		return true;
	}



}
