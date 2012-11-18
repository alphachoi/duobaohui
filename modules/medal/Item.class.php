<?php
namespace Snake\Modules\Medal;

/**
 * 单个勋章信息
 * @author, Chen Hailong
 */

Use \Snake\Package\Medal\Medal as PMedal;			

/**
 * 单个勋章信息
 * @author, Chen Hailong
 */
class Item extends \Snake\Libs\Controller {
	private $medalId = NULL;
	private $userId = NULL;

	public function run() {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$medalHelpper = new PMedal();
		$data = $medalHelpper->getMedalInfoByMids(array($this->medalId));
		if (empty($data)) {
			return FALSE;
		}
		if (!empty($this->userId)) {
			$medalAddition = $medalHelpper->getMedalByUidAndMid($this->userId, $this->medalId);
		}
        if (!empty($medalAddition)) {
            $data[$this->medalId] = array_merge($data[$this->medalId], $medalAddition[$this->medalId]);
        }   
        else {
            $data[$this->medalId]['user_id'] = $this->userId;
            $data[$this->medalId]['update_time'] = 0;
        }  
		$userNumber = $medalHelpper->getMedalGotNum($this->medalId);
		$data[$this->medalId]['got_number'] = $userNumber;
		//检查该勋章是否expired
		$data[$this->medalId]['available'] = $medalHelpper->checkMedalAvailable($this->medalId);
		//print_r($data[$this->medalId]);exit;
		$this->view = $data[$this->medalId];
	}
	
	private function _init() {
		$this->medalId = isset($this->request->REQUEST['medal_id']) ? $this->request->REQUEST['medal_id'] : 0;
		if (empty($this->medalId) || !is_numeric($this->medalId) || $this->medalId <= 0) {
			$this->setError(400, 40301, 'medal id is illeage');
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		
		return TRUE;
	}
	
}
