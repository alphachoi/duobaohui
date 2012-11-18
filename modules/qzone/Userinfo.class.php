<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\Special\User;


class Userinfo extends \Snake\Libs\Controller {

	private $openId = NULL;
	private $openKey = NULL;
	private $time = 0;
	private $params = array();
	
	public function run() {
		$this->_init();
		$userHelper = new User($this->params, $this->time);
		$userInfo = $userHelper->getUser();
		$this->view = $userInfo;
		return TRUE;

		
	}

	private function _init() {
		$this->params['open_id'] = isset($this->request->REQUEST['openid']) ? $this->request->REQUEST['openid'] : 0;
		$this->params['open_key'] = isset($this->request->REQUEST['openkey']) ? $this->request->REQUEST['openkey'] : 0;
		$this->params['app_id'] = "100666641";
		$this->params['app_key'] = "e534c012418167558b4bdcf926347c91";
		$cookie = array(
			'SEASHELL' => $this->request->seashell
		);
		$this->params['cookie'] = $cookie;
		if (empty($this->params['open_id']) && empty($this->params['open_id'])) {
			$this->time = 1;
			return FALSE;
		}
		return TRUE;
	}

}
