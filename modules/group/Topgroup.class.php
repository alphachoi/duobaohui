<?php
namespace Snake\Modules\Group;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupFactory AS GroupFactory;
use \Snake\Package\Group\TopGroup AS TopGroupHelper;
use \Snake\Package\User\User AS User;

class Topgroup extends \Snake\Libs\Controller {
	private $memcache = NULL;
	
	public function run() {
		$this->memcache = Memcache::instance();
		$this->main();
	}


	public function main() {
	
		//右边栏top10 -start
		$topGroupInfo = array();
		$cacheKey = "GROUP__TOP_TEN_INFO";
		$topGroupInfo = $this->memcache->get($cacheKey);
		if (!empty($topGroupInfo)) {
			$this->view = $topGroupInfo;
			return;
		}
		$groupId = array();
		$groupId = array(88848, 75268, 10020, 10025, 10211, 10695, 29411, 11827,14481,10825);
		/*
		$groupHelper = new TopGroupHelper();
		$groupHelper->getTopGroupInfo();
		$groupId = $groupHelper->getGroupIds();*/
		$groupHandle = new GroupFactory($groupId);
		$topGroupInfoObj = $groupHandle->getGroups();
		$topGroupInfo = array();
		$adminIds = array();
		foreach ($topGroupInfoObj AS $key) {
			$topGroupInfo[] = $key->getGroup();
		}
		foreach ($topGroupInfo AS $key => $value) {
			$adminIds[] = $topGroupInfo[$key]['admin_uid'];
		}
		$userHelper = new User();
		$userInfo = $userHelper->getUserInfos($adminIds);
		foreach ($topGroupInfo AS $key => $value) {
			$topGroupInfo[$key]['admin_nickname'] = $userInfo[$topGroupInfo[$key]['admin_uid']]['nickname'];
		}
		$this->view = $topGroupInfo;
		$this->memcache->set($cacheKey, $topGroupInfo, 3600*24);
		return ;
	
	}
}
