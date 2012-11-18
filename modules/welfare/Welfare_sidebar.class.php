<?php
namespace  Snake\Modules\Welfare;

use  Snake\Package\Welfare\Welfare;
use  Snake\libs\Cache\Memcache;
use  Snake\Package\Picture\PictureConvert;
use  Snake\Package\User\User;
use  Snake\Package\Welfare\SideBar;

class Welfare_sidebar extends \Snake\Libs\Controller {
		
	private $welfareHelper;	

	public function run() {
		$this->welfareHelper = Welfare::getInstance();
		//参加福利社的总人数
		/*
		$cache = Memcache::instance();
		$key = 'Welfare_header_allnum';
		$num = $cache->get($key);
		if (empty($num)) {
			$num = $this->welfareHelper->getWelfareAllNum();
			$num = $num[0]['num'];
			$cache->set($key, $num, 3600);
		}
		*/
		//$info['num'] = $num;
		//福利社时间
		/*
		$welfareTime = $_SERVER['REQUEST_TIME'] - mktime(11,60,60,11,10,2011);
		$welfareTime = (int)($welfareTime/3600/24);
		$info['day'] = $welfareTime;	
       */
		//获得5最新参加福利社的人
		$sider = new SideBar();
		$info['apply'] = $sider->getNewTakeInWelfareInfo(5,0);
		//获奖名单
	//	$info['success'] = $sider->getNewTakeInWelfareInfo(5,1);
	    $this->view = $info;	
	}
}
