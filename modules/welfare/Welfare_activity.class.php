<?php
namespace  Snake\Modules\Welfare;

use  \Snake\Package\Welfare\Welfare AS Welfare;
use \Snake\Package\User\User AS User;
use  \Snake\Package\Picture\PictureConvert AS PictureConvert;

class Welfare_activity extends \Snake\Libs\Controller {
	private $userid;
	private $welfareHelper;
	private $aid;
	private $userHelper;
	public function run() {
		$this->aid = $this->request->path_args[0];
		$this->userid = $this->userSession['user_id'];
		if (empty($this->aid)) {
			return FALSE;	
		}
		$this->welfareHelper = Welfare::getInstance();
		$info = $this->getActivityInfo();
		$this->view = $info;
		//获得微博信息
	}
	public function getActivityInfo() {
	    $info = $this->welfareHelper->getWelfareInfoById('*',$this->aid);
		$backInfo = array();
		//参加活动的人数
		$num = $this->welfareHelper->getApplyInfoById('count(*) as num', $this->aid);
		$info[0]['participate_num'] = $num[0]['num'];
        //获得参与讨论的人数
	    $tnum = $this->welfareHelper->getTwitterById('count(*) as num', $this->aid);
		$info[0]['discuss_num'] = $tnum[0]['num'];
		//获得活动状态
		$begin = $this->getActivityBegin($info); 
		$info[0]['begin'] = $begin;
		//获得图片的地址
	    $pictureHelper = new PictureConvert($info[0]['index_banner']);
	    $info[0]['activity_banner']= $pictureHelper->getPictureO();
		//获取用户的信息
		if (!empty($this->userid)) {
			$this->userHelper = new User();
			$colum = array('nickname','realname','email','mobile','shipping_address','avatar_a');
			$userInfo = $this->userHelper->getUserInfo($this->userid,$colum);
		}
		$info[0]['email'] = $userInfo['email'];
		$info[0]['mobile'] = $userInfo['mobile'];
		$info[0]['shipping_address'] = $userInfo['shipping_address'];
		$info[0]['realname'] = $userInfo['realname'];
		$info[0]['begin_time'] = date("Y.m.d", $info[0]['begin_time']);
		$info[0]['end_time'] = date("Y.m.d", $info[0]['end_time']);
		$info[0]['products_status'] = 'old';  
		$info[0]['hasAvatar'] = !strpos($info[0]['avatar_a'], "/css/images/0.gif"); 
		if (!empty($info[0]['products_img'])) {
			 $pictureHelper  = new PictureConvert($info[0]['products_img']);
			 $info[0]['products_img'] = $pictureHelper->getPictureO();
			 $info[0]['products_status'] = 'new';
		}
		$backInfo = $info[0];
		return $backInfo;
	}
	public function getActivityBegin($activityInfo) {
		//活动逻辑判定
		$isend = 0;
		$activityInfoSets['begin'] = 1; //我要申请
		if ($_SERVER['REQUEST_TIME'] < $activityInfo[0]['begin_time']){
			$activityInfoSets['begin'] = 0; //即将开始
		}elseif( $_SERVER['REQUEST_TIME'] > $activityInfo[0]['end_time']){
			$activityInfoSets['begin'] = 4; //活动结束
			$isend = 1;
		}
		if (!empty($this->userid)) {
			$isapply = $this->welfareHelper->getOneWelfare('status',$this->aid, $this->userid);	
			if (!empty($isapply)) {
				$activityInfoSets['begin'] = 2;//申请中
				if ($isapply[0]['status'] == 1) {
					$$activityInfoSets['begin'] = 3;//申请通过
				}
				elseif ($isend == 1) {
					$$activityInfoSets['begin'] = 4;	
				}
			}
		}
		return $activityInfoSets['begin'];
	}
		
		
}
