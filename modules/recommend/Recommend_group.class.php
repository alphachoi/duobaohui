<?php
namespace Snake\Modules\Recommend;
/**
 *推荐杂志
 *author gstan
 *version 1
 *email guoshuaitan@meilishuo.com
 *date 2012-08-13
 */
use Snake\Package\Recommend\Recommend;
use Snake\Package\Group\Groups;
use Snake\Package\Group\GroupTwitters;

class Recommend_group extends \Snake\Libs\Controller {
	private $user_id;
	private $num;
	private $groupId;
	public function run() {
		$this->_init();
		$groupPic = $this->recommendGroupForUser();
		$this->view = $groupPic;

	}	
	public function recommendGroupForUser() {
		$recommendHelper = new Recommend();
        $rec_groups = $recommendHelper->getReGroupByUid($this->user_id, $this->num);
		$groupIds = array();
		$groupIds = \Snake\Libs\Base\Utilities::DataToArray($rec_groups, 'group_id');
		if (empty($groupIds)) {
			$this->setError(404, 400401, "no recommend group");
			return false;	
		}
		$groupHelper = new Groups();
		$groupPic = array();
		$groupPic = $groupHelper->getGroupSquareInfo($groupIds);
		return $groupPic;
		
	}
	public function _init() {
		$this->num = isset($this->request->path_args[0]) ? $this->request->path_args[0] : 5;
		$this->user_id = $this->userSession['user_id'];
		if (empty($this->user_id)) {
			$this->setError(404,400402, "no login in");
			return false;	
		}	
	}
	
	
	
	
}
