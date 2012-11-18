<?php
namespace Snake\Modules\Person;
use \Snake\Package\Person\Profile;

class Add_like extends \Snake\Libs\Controller {
	private $userId;
	private $goodsId;
	public function run()  {
		$init = $this->_init();
		if(!$init) return false;

		$isAdd = Profile::getInstance()->addLike($this->userId, $this->goodsId);
		$this->view = array('status' => $isAdd);
	}

	private function _init() {
		//$this->userId = $this->userSession['user_id'];
		$this->userId = 1;
		if (empty($this->userId)) {
			$this->setError(400, 40101, 'please login first, then add userId');
			return FALSE;
		}
		$this->goodsId = 1;
		//$this->goodsId = !empty($this->request->REQUEST['goodsId']) ? $this->request->REQUEST['goodsId'] : 0;
		if (empty($this->goodsId)) {
			$this->setError(400, 40101, 'please login first, then add goodsId');
			return FALSE;
		}
		return TRUE; 
	}
}
