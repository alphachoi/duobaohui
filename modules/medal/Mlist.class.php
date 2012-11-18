<?php
namespace Snake\Modules\Medal;

/**
 * 勋章列表页面
 * @author, Chen Hailong
 */

Use \Snake\Package\Medal\Medal as PMedal;			
Use \Snake\Libs\Cache\Memcache;

/**
 * 勋章列表页面
 * @author, Chen Hailong
 */
class Mlist extends \Snake\Libs\Controller {
	private $type = NULL;
	private $visitedUserId = NULL;
	private $cache = 0; //TRUE;
	private $typeOptions = array(1, 2, 3, 5);


	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$medals = $this->_getMedalList();
		if (empty($medals)) {
			return FALSE;
		}
		//print_r($medals);die;
		$this->view = $medals;
	}
	
	private function _init() {
		$this->type = isset($this->request->REQUEST['type']) ? $this->request->REQUEST['type'] : 0;
		if (!empty($this->type) && !in_array($this->type, $this->typeOptions)) {
			$this->setError(400, 40101, 'type is illeage');
			return FALSE;
		}
		$this->visitedUserId = isset($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($this->visitedUserId)) {
			$this->visitedUserId = $this->userSession['user_id'];
		}

		return TRUE;
	}
	
	/**
	 *	cache handle
	 **/
	private function _getMedalList() {
		$cacheKey = 'medal:list_' . $this->type . '_uid_' . $this->visitedUserId;
		$cacheHelper = Memcache::instance();
		$data = $cacheHelper->get($cacheKey);
		if ($this->cache && !empty($data)) {
			return $data;
		}
		$medalHelpper = new PMedal();
		$data = $medalHelpper->getMedalListInfo($this->type, $this->visitedUserId);

		if (empty($data)) {
			$this->setError(400, 40104, 'There is no medal base on type: ' . $this->type);
			return FALSE;
		}
		$cacheHelper->set($cacheKey, $data, 3600);
		return $data;
	}

}
