<?php
namespace Snake\Modules\Person;

/**
 * 个人页面，二级菜单
 * @author, Chen Hailong
 **/

Use \Snake\Package\User\User;			
Use \Snake\Package\Group\GroupUser;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Package\Group\Helper\RedisUserGroupFollower; 
Use \Snake\Package\Twitter\Twitter;

class Second_menu extends \Snake\Libs\Controller {
	private $visitedUserId = NULL;
	private $cache = 0; //TRUE;
	private $numInfo = array();

	public function run()  {
		$init = $this->_init();
		if (empty($init)) {
			return FALSE;
		}
		$data = $this->_getSecondMenu();
		if (empty($data)) {
			$this->view = array();
			return FALSE;
		}
		//print_r($this->numInfo);die;
		$this->view = $this->numInfo;
	}
	
	private function _init() {
		//current login userId
		$this->visitedUserId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($this->visitedUserId)) {
			$this->setError(400, 40101, 'userId is empty');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 *	data handle
	 **/
	private function _getSecondMenu() {
		$uHelper = new GroupUser();
		$editorNum = (int) $uHelper->getUserGroupNumber($this->visitedUserId, array(0,1));
        $client = ZooClient::getClient();
        $likeData = $client->user_likes_twitters($this->visitedUserId, 0, 120);
		$cacheHelper = Memcache::instance();
		$cacheHelper->set('person:share_data' . $this->visitedUserId, $likeData, 120);
		$likeNum = (int) $likeData['total'];
		$followNum = (int) RedisUserGroupFollower::lSize($this->visitedUserId);
		$tObj = new Twitter();
		$shareNum = (int) $tObj->getNumOfTwitterByUid($this->visitedUserId);
		//$shareNum = count($tObj->getPicTwitterByUid($this->visitedUserId, 0, 10000));
		$this->numInfo = array('editorNum' => $editorNum, 'shareNum' => $shareNum, 
							'likeNum' => $likeNum, 	'followNum' => $followNum);		
		return TRUE; 
	}

}
