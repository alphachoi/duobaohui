<?php
namespace Snake\Modules\Medal;

/**
 * 获取过指定勋章的MM
 * @author, Chen Hailong
 */

Use \Snake\Package\Medal\Medal as PMedal;			
Use \Snake\Libs\Cache\Memcache;

/**
 * 获取过指定勋章的MM
 * @author, Chen Hailong
 */
class User_list extends \Snake\Libs\Controller {
	private $medalId = NULL;
	private $page = 0;
	private $pageSize = 180;
	private $userId = NULL;
	private $cache = 0; //TRUE;

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$medalHelpper = new PMedal();
		$data = $medalHelpper->getMedalGotMms($this->medalId, $this->page, $this->pageSize);
		if (!empty($data)) {
			$data = array_values($data);
		}
        $userNumber = $medalHelpper->getMedalGotNum($this->medalId);
        $data['got_number'] = $userNumber;
		if (empty($data)) {
			return FALSE;
		}
		//print_r($data);exit;
		$this->view = $data;
	}
	
	private function _init() {
		$this->medalId = isset($this->request->REQUEST['medal_id']) ? $this->request->REQUEST['medal_id'] : 0;
		if (empty($this->medalId) || !is_numeric($this->medalId) || $this->medalId <= 0) {
			$this->setError(400, 40301, 'medal id is illeage');
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		
		$this->page = isset($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
		if (!is_numeric($this->page) || $this->page < 0) {
			$this->setError(400, 40302, 'page is illeage');
			return FALSE;
		}

		return TRUE;
	}
	
}
